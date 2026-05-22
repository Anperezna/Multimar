<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ---------------------------------------------------------
        // FASE 1: Tablas independientes (Sin claves foráneas externas)
        // ---------------------------------------------------------

        Schema::create('paissos', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
        });

        Schema::create('rols', function (Blueprint $table) {
            $table->id();
            $table->string('rol');
        });

        Schema::create('tipus_document', function (Blueprint $table) {
            $table->id();
            $table->string('tipus_document');
        });

        Schema::create('estats_ofertes', function (Blueprint $table) {
            $table->id();
            $table->string('estat');
        });

        Schema::create('tracking_steps', function (Blueprint $table) {
            $table->id();
            $table->integer('ordre');
            $table->string('nom');
        });

        Schema::create('tipus_incoterms', function (Blueprint $table) {
            $table->id();
            $table->string('codi');
            $table->string('nom');
        });

        Schema::create('tipus_transports', function (Blueprint $table) {
            $table->id();
            $table->string('tipus');
        });

        Schema::create('tipus_contenidors', function (Blueprint $table) {
            $table->id();
            $table->string('tipus');
        });

        Schema::create('tipus_fluxes', function (Blueprint $table) {
            $table->id();
            $table->string('tipus');
        });

        Schema::create('tipus_carrega', function (Blueprint $table) {
            $table->id();
            $table->string('tipus');
        });

        Schema::create('tipus_validacions', function (Blueprint $table) {
            $table->id();
            $table->string('tipus');
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('foto_user')->nullable();
            $table->string('foto_dni_front')->nullable();
            $table->string('foto_dni_back')->nullable();
        });

        // ---------------------------------------------------------
        // FASE 2: Tablas con dependencias de primer nivel
        // ---------------------------------------------------------

        Schema::create('ciutats', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('pais_id')->constrained('paissos')->onDelete('cascade');
        });

        Schema::create('tipus_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('tipus_nom');
            $table->foreignId('tracking_steps_id')->constrained('tracking_steps')->onDelete('cascade');
        });

        Schema::create('incoterms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipus_inconterm_id')->constrained('tipus_incoterms')->onDelete('cascade');
            $table->foreignId('tracking_steps_id')->constrained('tracking_steps')->onDelete('cascade');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('tipus_document_id')->constrained('tipus_document')->onDelete('cascade');
        });

        // ---------------------------------------------------------
        // FASE 3: Tablas con dependencias de segundo nivel
        // ---------------------------------------------------------

        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('ciutat_id')->constrained('ciutats')->onDelete('cascade');
        });

        Schema::create('linies_transport_maritim', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('ciutat_id')->constrained('ciutats')->onDelete('cascade');
        });

        Schema::create('transportistes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('ciutat_id')->constrained('ciutats')->onDelete('cascade');
        });

        Schema::create('usuaris', function (Blueprint $table) {
            $table->id();
            $table->string('correu')->unique();
            $table->string('contrasenya');
            $table->string('nom');
            $table->string('cognoms');
            $table->foreignId('rol_id')->constrained('rols')->onDelete('cascade');
            $table->foreignId('pais_id')->nullable()->constrained('paissos')->onDelete('set null');
            $table->string('empresa')->nullable();
            $table->string('dni')->nullable();
            $table->string('foto_user')->nullable();
            $table->string('foto_dni_front')->nullable();
            $table->string('foto_dni_back')->nullable();
            // Self-referencing FK para supervisor/subordinat
            $table->foreignId('usuari_id')->nullable()->constrained('usuaris');
        });

        Schema::create('liniasmaritim_ports', function (Blueprint $table) {
            $table->id();
            
            // CORRECCIÓN SQL SERVER: Quitamos los cascade para evitar el bucle con "ciutats"
            $table->foreignId('linia_transport_maritim_id')->constrained('linies_transport_maritim');
            $table->foreignId('port_id')->constrained('ports');
            
            $table->string('nom_linia_transport_maritim')->nullable();
        });

        // ---------------------------------------------------------
        // FASE 4: Dependencias profundas (Notificaciones, Chatbot, Solicitudes)
        // ---------------------------------------------------------

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->boolean('visto')->default(false);
            $table->foreignId('usuari_id')->constrained('usuaris')->onDelete('cascade');
        });

        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuari_id')->constrained('usuaris')->onDelete('cascade');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->text('prompt');
            $table->text('reply')->nullable();
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitud', function (Blueprint $table) {
            $table->id();
            $table->string('mercancia_nombre');
            $table->decimal('pes_brut', 10, 2)->nullable();
            $table->decimal('volum', 10, 2)->nullable();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            // CORRECCIÓN SQL SERVER: Quitamos set null / cascade de las FK problemáticas
            $table->foreignId('operador_id')->nullable()->constrained('usuaris');
            $table->string('mercancia_tipus')->nullable();
            $table->foreignId('tipus_transport_id')->constrained('tipus_transports');
            $table->foreignId('tipus_contenidor_id')->nullable()->constrained('tipus_contenidors');
            $table->foreignId('origen_id')->constrained('ciutats');
            $table->foreignId('destino_id')->constrained('ciutats');
            $table->foreignId('incoterm_id')->nullable()->constrained('incoterms');
            $table->foreignId('tipus_fluxe_id')->nullable()->constrained('tipus_fluxes');
            $table->foreignId('tipus_carrega_id')->nullable()->constrained('tipus_carrega');
            $table->foreignId('tracking_step_id')->nullable()->constrained('tracking_steps'); // <--- El culpable arreglado
        });

        // ---------------------------------------------------------
        // FASE 5: Ofertas (La tabla central que une casi todo)
        // ---------------------------------------------------------

        Schema::create('ofertes', function (Blueprint $table) {
            $table->id();
            
            // CORRECCIÓN SQL SERVER: Evitamos triángulos con solicitud
            $table->foreignId('tipus_transport_id')->constrained('tipus_transports'); 
            
            $table->text('comentaris')->nullable();
            $table->foreignId('agent_comercial_id')->nullable()->constrained('usuaris');
            $table->foreignId('transportista_origen_id')->nullable()->constrained('transportistes');
            $table->foreignId('tipus_validacio_id')->nullable()->constrained('tipus_validacions');
            $table->foreignId('port_origen_id')->nullable()->constrained('ports');
            $table->foreignId('port_desti_id')->nullable()->constrained('ports');
            $table->foreignId('linia_transport_maritim_id')->nullable()->constrained('linies_transport_maritim');
            $table->foreignId('estat_oferta_id')->nullable()->constrained('estats_ofertes');
            $table->date('data_creacio')->nullable();
            $table->date('data_validessa_inicial')->nullable();
            $table->date('data_validessa_fina')->nullable();
            $table->text('rao_rebuig')->nullable();
            
            // La única cascada directa y segura que dejamos
            $table->foreignId('solicitud_id')->constrained('solicitud')->onDelete('cascade');
            
            $table->foreignId('documents_id')->nullable()->constrained('documents');
            $table->dateTime('etd')->nullable();
            $table->dateTime('eta')->nullable();
            $table->foreignId('transportista_destino_id')->nullable()->constrained('transportistes');
            $table->foreignId('operador_id')->nullable()->constrained('usuaris');
            
            $table->boolean('acceptat')->default(false);
            $table->boolean('vist')->default(false);
            $table->boolean('acabat')->default(false);
            $table->boolean('cancelat')->default(false);
            
            // Evitamos triángulo con solicitud
            $table->foreignId('tracking_step_id')->nullable()->constrained('tracking_steps'); 
        });
    }

    public function down()
    {
        // El método down DEBE ejecutarse en el orden exactamente inverso para no violar las FK
        Schema::dropIfExists('ofertes');
        Schema::dropIfExists('solicitud');
        Schema::dropIfExists('chatbot_messages');
        Schema::dropIfExists('notificaciones');
        
        // --- AÑADE ESTO AQUÍ ---
        Schema::dropIfExists('liniasmaritim_ports');
        // -----------------------

        Schema::dropIfExists('usuaris');
        Schema::dropIfExists('transportistes');
        Schema::dropIfExists('linies_transport_maritim');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('incoterms');
        Schema::dropIfExists('tipus_tracking');
        Schema::dropIfExists('ciutats');

        Schema::dropIfExists('clients');
        Schema::dropIfExists('tipus_validacions');
        Schema::dropIfExists('tipus_carrega');
        Schema::dropIfExists('tipus_fluxes');
        Schema::dropIfExists('tipus_contenidors');
        Schema::dropIfExists('tipus_transports');
        Schema::dropIfExists('tipus_incoterms');
        Schema::dropIfExists('tracking_steps');
        Schema::dropIfExists('estats_ofertes');
        Schema::dropIfExists('tipus_document');
        Schema::dropIfExists('rols');
        Schema::dropIfExists('paissos');
    }
};
