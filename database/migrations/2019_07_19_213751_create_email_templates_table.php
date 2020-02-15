<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rupalipshinde\Template\TemplateModel;

class CreateEmailTemplatesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('subject');
            $table->text('description');
            $table->string('language', 5)->default('en-us');
            $table->text('placeholder')->nullable();
            $table->string('event');
            $table->enum('status', ['0', '1'])->default('0');
            $table->string('mail_to')->default('A');
            $table->timestamps();
            $table->softDeletes();
        });

        $emailTemplates = array(
            array('id' => '1',
                'title' => 'Notification to Simplex Admin for Password Reset (Forgot Password)',
                'subject' => 'Reset your password',
                'description' => '<p>Hello [SUPER_ADMIN_FIRST_NAME] [SUPER_ADMIN_LAST_NAME],</p>

                <p>We recently received a request to reset your [PORTAL_NAME] password.</p>

                <p>Click here to change your password.</p>

                <p>[PASSWORD_RESET_URL]</p>                            

                <p>Account URL: [PORTAL_ADDRESS]</p>

                <p>Thanks,</p>

                <p>[PORTAL_NAME] Team</p>',
                'language' => 'en', 
                'placeholder' =>  '{"SUPER_ADMIN_FIRST_NAME" :"translations.super_admin_first_name","SUPER_ADMIN_LAST_NAME" :"translations.super_admin_last_name","PORTAL_NAME":"translations.portal_name","PORTAL_ADDRESS":"translations.portal_address"}',
                'event' => 'forgot_password', 
                'status' => '1',
                'mail_to' => 'A'),
        );
        TemplateModel::insert($emailTemplates);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('email_templates');
    }

}
