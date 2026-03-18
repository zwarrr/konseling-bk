<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\BkAccount;
use App\Models\Program;
use App\Models\ProgramBooking;
use App\Models\Users\UserNotification;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserProgramBookingController extends Controller
{
    private const SYSTEM_GURU_ACCOUNT_ID = 'EKON';
    private const SYSTEM_NAME = 'E-Konseling';

    private function buildSystemRoomId(string $siswaAccountId, string $siswaName): string
    {
        $existing = Chat::where('siswa_account_id', $siswaAccountId)
            ->where('guru_account_id', self::SYSTEM_GURU_ACCOUNT_ID)
            ->value('room_id');
        if ($existing) return $existing;

        $si = strtoupper(str_pad(
            substr(preg_replace('/[^a-zA-Z]/', '', $siswaName), 0, 2),
            2,
            'X'
        ));

        $prefix = 'RC' . $si . 'EK';
        $seq = 1;
        while (Chat::where('room_id', $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT))->exists()) {
            $seq++;
        }
        return $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT);
    }

    private function assertSiswa(): void
    {
        abort_unless((auth()->user()->role ?? 'siswa') !== 'guru', 403);
    }

    private function assertGuru(): void
    {
        abort_unless(auth()->user()?->role === 'guru', 403);
    }

    private function buildDirectRoomId(string $siswaName, string $siswaAccountId, BkAccount $bk): string
    {
        $existing = Chat::where('siswa_account_id', $siswaAccountId)
            ->where('guru_account_id', $bk->account_id)
            ->value('room_id');
        if ($existing) return $existing;

        $si = strtoupper(str_pad(
            substr(preg_replace('/[^a-zA-Z]/', '', $siswaName), 0, 2),
            2,
            'X'
        ));
        $gi = strtoupper(str_pad(
            substr(preg_replace('/[^a-zA-Z]/', '', $bk->name), 0, 2),
            2,
            'X'
        ));

        $prefix = 'RC' . $si . $gi;
        $seq = 1;
        while (Chat::where('room_id', $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT))->exists()) {
            $seq++;
        }
        return $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT);
    }

    /** POST /api/program/booking/{id}/chat-reconfirm */
    public function chatReconfirm(Request $request, int $id): JsonResponse
    {
        $this->assertSiswa();

        $data = $request->validate([
            'answer' => ['required', 'in:yes,no'],
        ]);

        $booking = ProgramBooking::with(['user', 'respondedBy'])
            ->whereKey($id)
            ->firstOrFail();

        abort_unless((int) $booking->user_id === (int) auth()->id(), 403);
        abort_unless($booking->status === 'approved', 422, 'Booking belum disetujui.');
        abort_unless(($booking->method ?? 'tatap_muka') === 'chat', 422, 'Booking ini bukan via chat.');
        abort_unless(($booking->booking_type ?? 'individu') === 'individu', 422, 'Konfirmasi chat hanya untuk individu.');
        abort_unless(!empty($booking->responded_by) && $booking->respondedBy, 422, 'BK belum ditentukan.');

        // Guard: only allow reconfirmation before schedule (and not too far away)
        $now = now();
        if ($booking->scheduled_at) {
            abort_if($booking->scheduled_at->isPast(), 422, 'Jadwal sudah lewat.');
            abort_if($booking->scheduled_at->diffInHours($now) > 6, 422, 'Terlalu jauh dari jadwal.');
        }

        if ($data['answer'] === 'yes') {
            ProgramBooking::whereKey($booking->id)->update([
                'chat_reconfirmed_at' => $now,
                'chat_reconfirm_declined_at' => null,
            ]);

            // Remove token from EKON message so buttons disappear on refresh
            if ($booking->user) {
                $sysRoomId = $this->buildSystemRoomId($booking->user->account_id, $booking->user->name ?? '?');
                $token = '[[EKON_CONFIRM:' . $booking->id . ']]';
                $msg = Chat::where('room_id', $sysRoomId)
                    ->where('message', 'like', '%' . $token . '%')
                    ->orderByDesc('id')
                    ->first();
                if ($msg) {
                    $clean = trim(str_replace($token, '', (string) $msg->message));
                    $clean = preg_replace('/\s{2,}/', ' ', $clean) ?? $clean;
                    $msg->update(['message' => $clean]);
                }
            }

            $siswa = $booking->user;
            $bk    = $booking->respondedBy;
            $roomId = $this->buildDirectRoomId($siswa?->name ?? '?', $siswa?->account_id ?? '', $bk);

            // IMPORTANT: do not create any Chat record here
            // so this room does not appear in chat list until a real message is sent.
            $url = route('chat.room', ['roomId' => $roomId, 'guru' => $bk->account_id]);
            return response()->json(['ok' => true, 'redirect' => $url]);
        }

        ProgramBooking::whereKey($booking->id)->update([
            'chat_reconfirm_declined_at' => $now,
        ]);

        // Remove token from EKON message so buttons disappear on refresh
        if ($booking->user) {
            $sysRoomId = $this->buildSystemRoomId($booking->user->account_id, $booking->user->name ?? '?');
            $token = '[[EKON_CONFIRM:' . $booking->id . ']]';
            $msg = Chat::where('room_id', $sysRoomId)
                ->where('message', 'like', '%' . $token . '%')
                ->orderByDesc('id')
                ->first();
            if ($msg) {
                $clean = trim(str_replace($token, '', (string) $msg->message));
                $clean = preg_replace('/\s{2,}/', ' ', $clean) ?? $clean;
                $msg->update(['message' => $clean]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /** POST /siswa/program/{slug}/booking */
    public function store(Request $request, string $slug): RedirectResponse
    {
        $this->assertSiswa();

        $program = Program::where('status', 'publish')->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'scheduled_at' => ['required', 'date_format:Y-m-d\\TH:i'],
            'message'      => ['nullable', 'string', 'max:500'],
            'booking_type' => ['nullable', 'in:individu,group'],
            'method'       => ['nullable', 'in:tatap_muka,chat'],
            'participants' => ['nullable', 'string', 'max:1500'],
        ]);

        $bookingType = $data['booking_type'] ?? 'individu';
        $method = $data['method'] ?? 'tatap_muka';

        if ($bookingType === 'group' && $method === 'chat') {
            return back()
                ->withErrors(['method' => 'Booking group wajib tatap muka (tidak bisa via chat).'])
                ->withInput();
        }

        if ($bookingType === 'individu' && $method === 'chat') {
            $rawParticipants = trim((string) ($data['participants'] ?? ''));
            if ($rawParticipants !== '') {
                return back()
                    ->withErrors(['participants' => 'Booking via chat hanya untuk individu (tanpa anggota).'])
                    ->withInput();
            }
        }

        $participants = null;
        if ($bookingType === 'group') {
            $raw = (string) ($data['participants'] ?? '');
            $items = preg_split('/\r\n|\r|\n|,/', $raw);
            $items = is_array($items) ? $items : [];
            $items = array_values(array_filter(array_map(fn($v) => trim((string) $v), $items)));
            if (count($items) < 1) {
                return back()
                    ->withErrors(['participants' => 'Isi minimal 1 nama anggota untuk booking group.'])
                    ->withInput();
            }
            $participants = $items;
        }

        $existingPending = ProgramBooking::where('program_id', $program->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();
        if ($existingPending) {
            return back()->with('booking_info', 'Kamu sudah mengirim permintaan jadwal. Tunggu persetujuan BK.');
        }

        $existingUpcomingApproved = ProgramBooking::where('program_id', $program->id)
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->where('scheduled_at', '>=', now())
            ->exists();
        if ($existingUpcomingApproved) {
            return back()->with('booking_info', 'Kamu sudah punya jadwal yang disetujui. Tunggu jadwal selesai sebelum booking lagi.');
        }

        ProgramBooking::create([
            'program_id'    => $program->id,
            'user_id'       => auth()->id(),
            'scheduled_at'  => Carbon::createFromFormat('Y-m-d\\TH:i', $data['scheduled_at']),
            'message'       => $data['message'] ?? null,
            'status'        => 'pending',
            'booking_type'  => $bookingType,
            'method'        => $method,
            'participants'  => $participants,
        ]);

        return back()->with('booking_success', 'Permintaan jadwal terkirim. Tunggu persetujuan BK.');
    }

    /** POST /api/program/booking/{id}/approve */
    public function approve(int $id): RedirectResponse
    {
        $this->assertGuru();

        $booking = ProgramBooking::with(['program', 'user'])->findOrFail($id);
        abort_if($booking->status !== 'pending', 422, 'Permintaan sudah diproses.');

        $program = $booking->program;
        abort_unless($program && (int) $program->added_by === (int) auth()->id(), 403);

        $booking->update([
            'status'       => 'approved',
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);

        // Notify siswa (in-app + push)
        $siswa = $booking->user;
        if ($siswa) {
            $schedStr = optional($booking->scheduled_at)->format('d M Y, H:i') ?? '';
            $labelType = ($booking->booking_type ?? 'individu') === 'group' ? 'group' : 'individu';
            $labelMethod = ($booking->method ?? 'tatap_muka') === 'chat' ? 'via chat' : 'tatap muka';
            $notifBody = 'Booking ' . $labelMethod . ' (' . $labelType . ') untuk program dan kegiatan "' . ($program->title ?? 'Program dan Kegiatan') . '" telah disetujui.';
            if ($schedStr) $notifBody .= ' Jadwal: ' . $schedStr . '.';
            if (($booking->booking_type ?? 'individu') === 'group' && !empty($booking->participants)) {
                $list = collect($booking->participants)->take(8)->implode(', ');
                $extra = collect($booking->participants)->count() > 8 ? ' (+lainnya)' : '';
                $notifBody .= ' Peserta: ' . $list . $extra . '.';
            }

            UserNotification::create([
                'user_id'      => $siswa->id,
                'user_type'    => 'siswa',
                'type'         => 'program_booking_approved',
                'title'        => 'Booking Disetujui',
                'body'         => $notifBody,
                'related_id'   => $booking->id,
                'related_type' => 'program_booking:' . $booking->id,
            ]);

            app(PushNotificationService::class)->sendToUser(
                $siswa->id,
                'Booking Disetujui',
                $notifBody,
                ['url' => '/siswa/program/' . ($program->slug ?? '')],
                'siswa'
            );

            // Also create a system chat message so E-Konseling always appears in siswa chat list.
            $schedStr = optional($booking->scheduled_at)->format('d M Y, H:i') ?? '';
            $labelMethod = ($booking->method ?? 'tatap_muka') === 'chat' ? 'via chat' : 'tatap muka';
            $labelType = ($booking->booking_type ?? 'individu') === 'group' ? 'Kelompok' : 'Individu';
            $bidang = trim((string) ($program?->category ?? ''));

            $programTitle = $program->title ?? 'Program dan Kegiatan';
            $line1 = 'Booking ' . $labelType . ' ' . $labelMethod . ': "' . $programTitle . '"';
            $line2 = ($bidang !== '' ? ('terkait bidang ' . strtolower($bidang) . ' ') : '') . 'sudah disetujui.';
            $line3 = 'Jadwal: ' . ($schedStr ?: '-') . '.';
            $msg = $line1 . "\n" . $line2 . "\n" . $line3;

            $roomId = $this->buildSystemRoomId($siswa->account_id, $siswa->name ?? '');
            Chat::create([
                'room_id'           => $roomId,
                'siswa_account_id'  => $siswa->account_id,
                'guru_account_id'   => self::SYSTEM_GURU_ACCOUNT_ID,
                'sender_account_id' => self::SYSTEM_GURU_ACCOUNT_ID,
                'sender_role'       => 'system',
                'message'           => $msg,
                'message_type'      => 'text',
                'status'            => 'unread',
            ]);
        }

        return back()->with('booking_success', 'Booking disetujui.');
    }

    /** POST /api/program/booking/{id}/reject */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $this->assertGuru();

        $booking = ProgramBooking::with(['program'])->findOrFail($id);
        abort_if($booking->status !== 'pending', 422, 'Permintaan sudah diproses.');

        $program = $booking->program;
        abort_unless($program && (int) $program->added_by === (int) auth()->id(), 403);

        $booking->update([
            'status'       => 'rejected',
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);

        $siswa = $booking->user;
        if ($siswa) {
            $labelType = ($booking->booking_type ?? 'individu') === 'group' ? 'group' : 'individu';
            $labelMethod = ($booking->method ?? 'tatap_muka') === 'chat' ? 'via chat' : 'tatap muka';
            $notifBody = 'Booking ' . $labelMethod . ' (' . $labelType . ') untuk program dan kegiatan "' . ($program->title ?? 'Program dan Kegiatan') . '" ditolak.';
            UserNotification::create([
                'user_id'      => $siswa->id,
                'user_type'    => 'siswa',
                'type'         => 'program_booking_rejected',
                'title'        => 'Booking Ditolak',
                'body'         => $notifBody,
                'related_id'   => $booking->id,
                'related_type' => 'program_booking:' . $booking->id,
            ]);

            app(PushNotificationService::class)->sendToUser(
                $siswa->id,
                'Booking Ditolak',
                $notifBody,
                ['url' => '/siswa/program/' . ($program->slug ?? '')],
                'siswa'
            );
        }

        return back()->with('booking_info', 'Booking ditolak.');
    }

    /** GET /api/program/booking/pending-count */
    public function pendingCount(): JsonResponse
    {
        $this->assertGuru();

        $count = ProgramBooking::whereHas('program', fn($q) => $q->where('added_by', auth()->id()))
            ->where('status', 'pending')
            ->count();

        return response()->json(['count' => $count]);
    }
}
