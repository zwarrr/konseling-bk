<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\GenericSystemNotificationMail;
use App\Models\ContactMessage;
use App\Models\ContactMessageTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query()->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $keyword = $request->string('q')->toString();
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%")
                    ->orWhere('message', 'like', "%{$keyword}%");
            });
        }

        $messages = $query->paginate(20, ['*'], 'messages_page')->appends($request->query());
        $topics = Schema::hasTable('contact_message_topics')
            ? ContactMessageTopic::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(3, ['*'], 'topics_page')
                ->appends($request->query())
            : collect();

        return view('admin.sections.data_pesan.index', compact('messages', 'topics'));
    }

    public function storeTopic(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:contact_message_topics,name'],
        ], [
            'name.required' => 'Nama topik wajib diisi.',
            'name.unique' => 'Nama topik sudah ada.',
        ]);

        $nextSort = (int) ContactMessageTopic::max('sort_order') + 1;
        ContactMessageTopic::create([
            'name' => trim($validated['name']),
            'is_active' => true,
            'sort_order' => $nextSort,
        ]);

        return redirect()->route('admin.messages.index')->with('success', 'Topik berhasil ditambahkan.');
    }

    public function updateTopic(Request $request, ContactMessageTopic $topic)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('contact_message_topics', 'name')->ignore($topic->id)],
        ], [
            'name.required' => 'Nama topik wajib diisi.',
            'name.unique' => 'Nama topik sudah ada.',
        ]);

        $topic->update([
            'name' => trim($validated['name']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.messages.index')->with('success', 'Topik berhasil diperbarui.');
    }

    public function destroyTopic(ContactMessageTopic $topic)
    {
        $topic->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Topik berhasil dihapus.');
    }

    public function reply(Request $request, ContactMessage $message)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'reply_message' => ['required', 'string', 'max:3000'],
        ], [
            'subject.required' => 'Subjek balasan wajib diisi.',
            'reply_message.required' => 'Isi balasan wajib diisi.',
        ]);

        Mail::to($message->email)->send(
            new GenericSystemNotificationMail(
                $validated['subject'],
                $validated['reply_message'],
                [
                    'original_topic' => (string) ($message->topic ?: 'Umum'),
                    'original_message' => (string) ($message->message ?: '-'),
                ]
            )
        );

        $message->update([
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => auth('admin')->id(),
            'reply_subject' => $validated['subject'],
            'reply_message' => $validated['reply_message'],
        ]);

        return redirect()
            ->route('admin.messages.index')
            ->with('success', 'Balasan berhasil dikirim ke email pengirim.');
    }
}
