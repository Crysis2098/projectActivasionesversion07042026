<style>

.acceso-denegado-wrapper{

    display:flex;

    justify-content:center;

    align-items:center;

    padding:60px 20px;

}

.acceso-denegado-card{

    background:#ffffff;

    border-radius:14px;

    box-shadow:0 6px 18px rgba(0,0,0,0.15);

    padding:40px;

    text-align:center;

    max-width:520px;

    width:100%;

    font-family:Poppins, sans-serif;

}

.acceso-icono{

    font-size:58px;

    margin-bottom:15px;

}

.acceso-titulo{

    font-size:26px;

    font-weight:600;

    margin-bottom:10px;

    color:#1e293b;

}

.acceso-texto{

    font-size:15px;

    color:#475569;

    margin-bottom:20px;

    line-height:1.6;

}

.acceso-info{

    background:#f1f5f9;

    padding:12px;

    border-radius:8px;

    font-size:14px;

    margin-bottom:20px;

    color:#334155;

}

.acceso-contacto{

    font-size:14px;

    color:#64748b;

    margin-top:15px;

    line-height:1.6;

}

</style>



<div class="acceso-denegado-wrapper">

    <div class="acceso-denegado-card">

        <div class="acceso-icono">🔒</div>

        <div class="acceso-titulo">Acceso restringido</div>

        <div class="acceso-texto">

            No cuentas con los permisos necesarios para acceder a esta sección del sistema.

        </div>

        <div class="acceso-info">

            ID de usuario: <strong><?php echo (int)$id_empleado; ?></strong>

        </div>

        <div class="acceso-contacto">

            Contacta a tu supervisor y/o al equipo de desarrollo si necesitas acceso.

        </div>

    </div>

</div>