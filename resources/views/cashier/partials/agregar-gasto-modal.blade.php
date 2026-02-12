{{-- Agregar gasto --}}
<form class="form-submit" id="form-agregar-gasto" action="{{ route('cashiers.expense.store') }}" method="post">
    @csrf
    <div class="modal modal-dark fade" tabindex="-1" id="agregar-gasto-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-dollar"></i> Agregar gasto</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <small>Categoría</small>
                        <select name="cashier_movement_category_id" id="select-cashier_movement_category_id" required class="form-control select2">
                            <option value="" disabled selected>--Seleccione una opción</option>
                            @foreach (App\Models\CashierMovementCategory::where('deleted_at', null)->get() as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <small>Monto</small>
                        <input type="number" name="amount" class="form-control" step="0.1" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <small>Descripción</small>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-submit">Registrar</button>
                </div>
            </div>
        </div>
    </div>
</form>