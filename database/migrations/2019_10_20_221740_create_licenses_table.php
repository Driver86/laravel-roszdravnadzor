<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Регистрационный номер
            $table->string('reg_number')->unique();
            // Дата регистрации
            $table->date('reg_date');
            // Сведения о лицензиате
            $table->text('name');
            // Место нахождения лицензиата
            $table->text('address');
            // Код ОГРН/ОГРИП
            $table->string('ogrn');
            // ИНН
            $table->string('inn');
            //  Код ОКПО
            $table->string('okpo');
            // Номер лицензии
            $table->string('license_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('licenses');
    }
}
