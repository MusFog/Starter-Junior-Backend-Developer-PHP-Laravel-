@extends('adminlte::page')

@section('title', 'Add Position')

@section('content_header')
<h1>Positions</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong class="mb-0">Add position</strong>
    </div>

    <div class="card-body">
        <form action="{{ route('positions.create') }}" method="POST">
            @csrf

            <input type="hidden" name="admin_id" value="{{ auth()->id() }}">

            <div class="form-group">
                <label for="position_name">Name</label>
                <input
                    type="text"
                    name="position_name"
                    id="position_name"
                    class="form-control @error('position_name') is-invalid @enderror"
                    value="{{ old('position_name') }}"
                >
                <small class="text-muted d-block text-right" id="char-count">0 / 256</small>

                @error('position_name')
                <span class="invalid-feedback" role="console.log">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('positions-list') }}" class="btn btn-outline-dark">Cancel</a>
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    $('#position_name').on('input', function () {
        $('#char-count').text(`${this.value.length} / 256`);
    }).trigger('input');
</script>
@endsection
