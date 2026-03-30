<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\GenericSystemNotificationMail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $messages = $query->paginate(20)->appends($request->query());

        return view('admin.sections.data_pesan.index', compact('messages'));
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
                $validated['reply_message']
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
