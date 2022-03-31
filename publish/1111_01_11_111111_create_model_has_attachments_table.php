<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

/**
 * model 拥有附件. 多对多多态
 */
class CreateModelHasAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('model_has_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->default(0)->comment('模型 ID');
            $table->char('subject_type', 64)->default('')->comment('模型');
            $table->unsignedBigInteger('attachment_id')->default(0)->comment('附件 ID');
            $table->tinyInteger('type')->default(1)->comment('用来标注使用类型;比如同一条数据里面会有主图和详情里的图,这里的 type 按照业务使用来定义');

            $table->index(['subject_id', 'subject_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_attachments');
    }
}
