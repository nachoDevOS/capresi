<select
    class="form-control select2"
    name="{{ $row->field }}"
    data-name="{{ $row->display_name }}"
    @if($row->required == 1) required @endif
>
    <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
</select>