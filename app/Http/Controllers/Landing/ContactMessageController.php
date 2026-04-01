<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactMessageTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    private function topicOptions(): array
    {
        if (!Schema::hasTable('contact_message_topics')) {
            return [];
        }

        return ContactMessageTopic::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function show()
    {
        $topicOptions = $this->topicOptions();

        return view('frontend.landingpage.sections.contact', compact('topicOptions'));
    }

    public function store(Request $request)
    {
        $topicOptions = $this->topicOptions();

        $topicRule = ['nullable', 'string', 'max:120'];
        if (!empty($topicOptions)) {
            $topicRule[] = Rule::in($topicOptions);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'topic' => $topicRule,
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'topic.in' => 'Topik yang dipilih tidak valid.',
            'message.required' => 'Pesan wajib diisi.',
            'message.max' => 'Pesan maksimal 1000 karakter.',
        ]);

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'topic' => $validated['topic'] ?? null,
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        return redirect()
            ->route('landing.contact')
            ->with('success', 'Pesan Anda berhasil dikirim. Tim BK akan segera merespons melalui email.');
    }
}
