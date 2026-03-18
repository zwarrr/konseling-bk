<?php

namespace App\Console\Commands;

use App\Models\BkAccount;
use App\Models\Chat;
use App\Models\ProgramBooking;
use App\Models\Users\UserNotification;
use App\Services\PushNotificationService;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;

class SendProgramBookingReminders extends Command
{
    private const SYSTEM_GURU_ACCOUNT_ID = 'EKON';
    private const SYSTEM_NAME = 'E-Konseling';

    protected $signature = 'program:booking-reminders
        {--window=5 : Window in minutes around each target time}
        {--lead=15 : Lead time (minutes) for same-day reminder before scheduled_at (tatap muka only)}';

    protected $description = 'Send program & kegiatan booking reminders at H-1 day, H-1 hour, and a same-day lead-time before schedule.';

    public function handle(): int
    {
        $windowMinutes = (int) $this->option('window');
        if ($windowMinutes < 1) $windowMinutes = 1;
        if ($windowMinutes > 60) $windowMinutes = 60;

        $leadMinutes = (int) $this->option('lead');
        if ($leadMinutes < 1) $leadMinutes = 1;
        if ($leadMinutes > 180) $leadMinutes = 180;

        $now = now();

        $countConfirm = $this->sendChatConfirmations($now, $windowMinutes);

        $countLead = $this->sendReminders(
            $now,
            $now->copy()->addMinutes($leadMinutes),
            $windowMinutes,
            'reminded_lead_at',
            'program_booking_reminder_lead',
            'Reminder Jadwal Sebentar Lagi',
            function (ProgramBooking $booking) use ($leadMinutes) {
                $programTitle = $booking->program?->title ?? 'Program dan Kegiatan';
                $bidang = $booking->program?->category ?: '-';
                $schedStr = optional($booking->scheduled_at)->format('d M Y, H:i') ?? '';

                $method = ($booking->method ?? 'tatap_muka') === 'chat' ? 'via chat' : 'tatap muka';
                $typeLabel = ($booking->booking_type ?? 'individu') === 'group' ? 'group' : 'individu';

                $needRuangBk = (($booking->method ?? 'tatap_muka') !== 'chat') || (($booking->booking_type ?? 'individu') === 'group');
                $line1 = 'Reminder: ' . $leadMinutes . ' menit lagi (harap sudah di Ruang BK)';
                $line2 = $programTitle . ' (' . $bidang . ')';
                $line3 = trim(($schedStr ? $schedStr . ' • ' : '') . $method . ' • ' . $typeLabel . ($needRuangBk ? ' • Ruang BK' : ''));
                return $line1 . "\n" . $line2 . "\n" . $line3;
            },
            fn($q) => $q->where('method', 'tatap_muka')
        );

        $count24 = $this->sendReminders(
            $now,
            $now->copy()->addHours(24),
            $windowMinutes,
            'reminded_24h_at',
            'program_booking_reminder_24h',
            'Reminder Jadwal Besok',
            function (ProgramBooking $booking) {
                $programTitle = $booking->program?->title ?? 'Program dan Kegiatan';
                $bidang = $booking->program?->category ?: '-';
                $schedStr = optional($booking->scheduled_at)->format('d M Y, H:i') ?? '';
                $method = ($booking->method ?? 'tatap_muka') === 'chat' ? 'via chat' : 'tatap muka';
                $typeLabel = ($booking->booking_type ?? 'individu') === 'group' ? 'group' : 'individu';

                $needRuangBk = (($booking->method ?? 'tatap_muka') !== 'chat') || (($booking->booking_type ?? 'individu') === 'group');
                $line1 = 'Reminder: besok ada jadwal';
                $line2 = $programTitle . ' (' . $bidang . ')';
                $line3 = trim(($schedStr ? $schedStr . ' • ' : '') . $method . ' • ' . $typeLabel . ($needRuangBk ? ' • Ruang BK' : ''));
                return $line1 . "\n" . $line2 . "\n" . $line3;
            }
        );

        $count1 = $this->sendReminders(
            $now,
            $now->copy()->addHour(),
            $windowMinutes,
            'reminded_1h_at',
            'program_booking_reminder_1h',
            'Reminder Jadwal 1 Jam Lagi',
            function (ProgramBooking $booking) {
                $programTitle = $booking->program?->title ?? 'Program dan Kegiatan';
                $bidang = $booking->program?->category ?: '-';
                $schedStr = optional($booking->scheduled_at)->format('d M Y, H:i') ?? '';
                $method = ($booking->method ?? 'tatap_muka') === 'chat' ? 'via chat' : 'tatap_muka';
                $method = $method === 'tatap_muka' ? 'tatap muka' : $method;
                $typeLabel = ($booking->booking_type ?? 'individu') === 'group' ? 'group' : 'individu';

                $needRuangBk = (($booking->method ?? 'tatap_muka') !== 'chat') || (($booking->booking_type ?? 'individu') === 'group');
                $isChatConfirm = ($booking->method ?? 'tatap_muka') === 'chat' && ($booking->booking_type ?? 'individu') === 'individu';
                if ($isChatConfirm) {
                    $line1 = 'Konfirmasi: 1 jam lagi jadwal via chat';
                    $line2 = $programTitle . ' (' . $bidang . ')';
                    $line3 = trim(($schedStr ? $schedStr . ' • ' : '') . 'via chat • individu');
                    // Hidden token for EKON room UI to render Ya/Tidak buttons.
                    $line3 .= ' [[EKON_CONFIRM:' . $booking->id . ']]';
                    return $line1 . "\n" . $line2 . "\n" . $line3;
                }

                $line1 = 'Reminder: 1 jam lagi (harap datang ke sekolah)';
                $line2 = $programTitle . ' (' . $bidang . ')';
                $line3 = trim(($schedStr ? $schedStr . ' • ' : '') . $method . ' • ' . $typeLabel . ($needRuangBk ? ' • Ruang BK' : ''));
                return $line1 . "\n" . $line2 . "\n" . $line3;
            }
        );

        $this->info("Done. Sent {$countConfirm} confirmations, {$countLead} (lead), {$count24} (H-1) and {$count1} (H-1 jam) reminders.");

        return self::SUCCESS;
    }

    private function sendReminders(CarbonInterface $now, CarbonInterface $target, int $windowMinutes, string $reminderColumn, string $type, string $title, callable $buildBody, ?callable $queryFilter = null): int
    {
        $start = $target->copy()->subMinutes($windowMinutes);
        $end   = $target->copy()->addMinutes($windowMinutes);

        $query = ProgramBooking::query()
            ->with(['program', 'user', 'respondedBy'])
            ->where('status', 'approved')
            ->whereBetween('scheduled_at', [$start, $end])
            ->whereNull($reminderColumn);

        if ($queryFilter) {
            $queryFilter($query);
        }

        $bookings = $query->get();

        if ($bookings->isEmpty()) {
            return 0;
        }

        $push = app(PushNotificationService::class);
        $sent = 0;

        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProgramBooking> $bookings */
        foreach ($bookings as $booking) {
            /** @var \App\Models\ProgramBooking $booking */
            // Guard against duplicates in race conditions
            $updated = ProgramBooking::whereKey($booking->id)
                ->whereNull($reminderColumn)
                ->update([$reminderColumn => $now]);

            if ($updated !== 1) {
                continue;
            }

            $siswaId = (int) $booking->user_id;
            $body = $buildBody($booking);

            UserNotification::create([
                'user_id'      => $siswaId,
                'user_type'    => 'siswa',
                'type'         => $type,
                'title'        => $title,
                'body'         => $body,
                'related_id'   => $booking->id,
                'related_type' => 'program_booking:' . $booking->id,
            ]);

            $slug = $booking->program?->slug ?? '';
            $isChat = ($booking->method ?? 'tatap_muka') === 'chat' && ($booking->booking_type ?? 'individu') === 'individu';
            $isSystemRoomPush = $isChat && $type === 'program_booking_reminder_1h';
            $sysRoomId = null;
            if ($isSystemRoomPush) {
                $siswa = $booking->user;
                if ($siswa) {
                    $sysRoomId = $this->buildSystemRoomId($siswa->account_id, $siswa->name ?? '?');
                }
            }
            $push->sendToUser(
                $siswaId,
                $title,
                $body,
                ['url' => ($isSystemRoomPush && $sysRoomId) ? route('chat.room', $sysRoomId) : ($isChat ? '/siswa/chat' : '/siswa/program/' . $slug)],
                'siswa'
            );

            // Also send as a system chat message from E-Konseling
            $this->sendSystemChatReminder($booking, $body);

            // Also notify BK (in-app + push)
            $bkId = (int) ($booking->responded_by ?? 0);
            if ($bkId > 0) {
                $siswaName = $booking->user?->name ?? 'Siswa';
                $programTitle = $booking->program?->title ?? 'Program dan Kegiatan';
                $bidang = $booking->program?->category ?: '-';
                $schedStr = optional($booking->scheduled_at)->format('d M Y, H:i') ?? '';

                $method = ($booking->method ?? 'tatap_muka') === 'chat' ? 'via chat' : 'tatap muka';
                $typeLabel = ($booking->booking_type ?? 'individu') === 'group' ? 'group' : 'individu';
                $needRuangBk = (($booking->method ?? 'tatap_muka') !== 'chat') || (($booking->booking_type ?? 'individu') === 'group');

                $line1 = 'Reminder jadwal';
                $line2 = $siswaName . ' • ' . $programTitle . ' (' . $bidang . ')';
                $line3 = trim(($schedStr ? $schedStr . ' • ' : '') . $method . ' • ' . $typeLabel . ($needRuangBk ? ' • Ruang BK' : ''));
                $bkBody = $line1 . "\n" . $line2 . "\n" . $line3;

                UserNotification::create([
                    'user_id'      => $bkId,
                    'user_type'    => 'bk',
                    'type'         => $type,
                    'title'        => $title,
                    'body'         => $bkBody,
                    'related_id'   => $booking->id,
                    'related_type' => 'program_booking:' . $booking->id,
                ]);

                $push->sendToUser(
                    $bkId,
                    $title,
                    $bkBody,
                    ['url' => $isChat ? '/bk/chat' : '/bk/chat'],
                    'bk'
                );
            }

            $sent++;
        }

        return $sent;
    }

    private function sendSystemChatReminder(ProgramBooking $booking, string $body): void
    {
        $siswa = $booking->user;
        if (!$siswa) return;

        $roomId = $this->buildSystemRoomId($siswa->account_id, $siswa->name ?? '?');

        Chat::create([
            'room_id'           => $roomId,
            'siswa_account_id'  => $siswa->account_id,
            'guru_account_id'   => self::SYSTEM_GURU_ACCOUNT_ID,
            'sender_account_id' => self::SYSTEM_GURU_ACCOUNT_ID,
            'sender_role'       => 'system',
            'message'           => $body,
            'message_type'      => 'text',
            'status'            => 'unread',
        ]);
    }

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

        // EK: E-Konseling initials
        $prefix = 'RC' . $si . 'EK';
        $seq = 1;
        while (Chat::where('room_id', $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT))->exists()) {
            $seq++;
        }
        return $prefix . str_pad($seq, 2, '0', STR_PAD_LEFT);
    }

    private function sendChatConfirmations(CarbonInterface $now, int $windowMinutes): int
    {
        $start = $now->copy()->subMinutes($windowMinutes);
        $end   = $now->copy()->addMinutes($windowMinutes);

        $bookings = ProgramBooking::query()
            ->with(['program', 'user', 'respondedBy'])
            ->where('status', 'approved')
            ->where('booking_type', 'individu')
            ->where('method', 'chat')
            ->whereNotNull('responded_by')
            ->whereBetween('scheduled_at', [$start, $end])
            ->whereNull('chat_confirmed_at')
            ->get();

        if ($bookings->isEmpty()) return 0;

        $sent = 0;
        foreach ($bookings as $booking) {
            $updated = ProgramBooking::whereKey($booking->id)
                ->whereNull('chat_confirmed_at')
                ->update(['chat_confirmed_at' => $now]);
            if ($updated !== 1) continue;

            $siswa = $booking->user;
            $bk    = $booking->respondedBy;
            if (!$siswa || !$bk) continue;

            $roomId = $this->buildDirectRoomId($siswa->name ?? '?', $siswa->account_id, $bk);
            $programTitle = $booking->program?->title ?? 'Program dan Kegiatan';
            $bidang = $booking->program?->category ?: '-';
            $schedStr = optional($booking->scheduled_at)->format('d M Y, H:i') ?? '';

            $msg = 'Halo ' . ($siswa->name ?? 'Siswa') . ', jadwal e-konseling via chat untuk program "' . $programTitle . '" sudah dimulai.';
            $msg .= ' Bidang: ' . $bidang . '.';
            if ($schedStr) $msg .= ' Jadwal: ' . $schedStr . '.';
            $msg .= ' Mohon konfirmasi, apakah kamu siap?';

            Chat::create([
                'room_id'           => $roomId,
                'siswa_account_id'  => $siswa->account_id,
                'guru_account_id'   => $bk->account_id,
                'sender_account_id' => $bk->account_id,
                'sender_role'       => 'guru',
                'message'           => $msg,
                'message_type'      => 'text',
                'status'            => 'unread',
            ]);

            app(PushNotificationService::class)->sendToUser(
                (int) $siswa->id,
                $bk->name ?? 'BK',
                $msg,
                ['url' => '/siswa/chat'],
                'siswa'
            );

            $sent++;
        }

        return $sent;
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
}
