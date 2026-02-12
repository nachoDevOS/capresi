{{-- <form action="#" id="rechazar_form" method="GET">
    {{ csrf_field() }}
    <div class="modal modal-primary fade" data-backdrop="static" id="decline-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa-solid fa-thumbs-down"></i> Desea rechazar el siguiente registro?</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center" style="text-transform:uppercase">
                        <i class="fa-solid fa-thumbs-down" style="color: #353d47; font-size: 5em;"></i>
                        <br>
                            
                        <p><b>Desea rechazar el siguiente registro?</b></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-dark pull-right delete-confirm" value="Sí, rechazar">
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</form> --}}


<form action="#" id="decline_form" method="GET">
    {{ csrf_field() }}
    <div class="modal modal-danger fade" data-backdrop="static" id="decline-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #f96868; color: white;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa-solid fa-ban"></i> Confirmar Rechazo
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="text-center" style="text-transform: uppercase;">
                        <div style="font-size: 5em; color: #f96868; margin-bottom: 15px;">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <h3 style="margin-top: 0; color: #333;">
                            <strong>¿CONFIRMAR RECHAZO?</strong>
                        </h3>
                        <p style="color: #666;">
                            Esta acción no podrá deshacerse. ¿Está seguro de rechazar este registro?
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger pull-right delete-confirm" style="background-color: #f96868; border-color: #f96868;">
                        <i class="fa-solid fa-thumbs-down"></i> Sí, Rechazar
                    </button>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
                        <i class="fa-solid fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
