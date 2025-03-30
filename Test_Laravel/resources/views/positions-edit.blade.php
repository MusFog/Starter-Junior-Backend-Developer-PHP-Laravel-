@extends('adminlte::page')

@section('title', 'Edit Position')

@section('content_header')
<h1>Positions</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <strong>Position edit</strong>
    </div>
    <div class="card-body">

        <form action="{{ route('positions.update') }}" method="POST">
            @csrf

            <input type="hidden" name="id" value="{{ $position->id }}">
            <input type="hidden" name="admin_id" value="{{ auth()->id() }}">

            <div class="form-group">
                <label for="position_name">Name</label>
                <input
                    type="text"
                    name="position_name"
                    id="position_name"
                    class="form-control @error('position_name') is-invalid @enderror"
                    value="{{ old('position_name', $position->name) }}"
                >
                <small class="text-muted d-block text-right" id="char-count">0 / 256</small>

                @error('position_name')
                <span class="invalid-feedback" role="console.log">{{ $message }}</span>
                @enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Created at:</strong> {{ $position->created_at->format('d.m.Y') }}</p>
                    <p><strong>Updated at:</strong> {{ $position->updated_at->format('d.m.Y') }}</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p><strong>Created by (admin ID):</strong> {{ $position->admin_created_id }}</p>
                    <p><strong>Updated by (admin ID):</strong> {{ $position->admin_updated_id }}</p>
                </div>
            </div>


            <div class="text-center">
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
