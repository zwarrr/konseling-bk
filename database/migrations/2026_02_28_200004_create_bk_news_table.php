<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bk_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable()->unique();    // SEO-friendly URL key
            $table->text('description')->nullable();
            $table->string('author', 100)->nullable();
            $table->string('img_card')->nullable();          // thumbnail / card image — required in practice
            $table->string('img_cards')->nullable();         // dedicated listing card thumbnail
            $table->string('img_detail_1')->nullable();      // optional detail image 1
            $table->string('img_detail_2')->nullable();      // optional detail image 2
            $table->string('status')->default('draft');      // 'publish' | 'draft'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_news');
    }
};
