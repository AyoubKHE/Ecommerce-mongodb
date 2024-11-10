<?php

use App\Models\SystemPermission;
use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('systemPermissions', function (Blueprint $collection) {});

            SystemPermission::insert([
                [
                    'name' => 'Produits',
                    'options' => array(
                        ['name' => 'Rien', 'value' => 0],
                        ['name' => 'Lire', 'value' => 1],
                        ['name' => 'Créer', 'value' => 2],
                        ['name' => 'Mettre à jour', 'value' => 4],
                        ['name' => 'Supprimer', 'value' => 8],
                        ['name' => 'Toutes', 'value' => -1],
                    ),
                ]
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systemPermissions');
    }
};
