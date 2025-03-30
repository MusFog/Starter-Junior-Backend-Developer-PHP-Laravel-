@extends('adminlte::page')

@section('title', 'Position List')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Positions</h1>
    <a href="{{ route('positions-list.create') }}" class="btn btn-dark">
        Add Position
    </a>
</div>
@endsection

@section('content')

@if(session('message'))
<div id="session-success" class="border border-success p-2 mb-2">
    {{ session('message') }}
</div>
@endif

@if(session('error'))
<div id="session-error" class="border border-danger p-2 mb-2">
    {{ session('error') }}
</div>
@endif

<table id="positions-table" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Name</th>
        <th>Last update</th>
        <th>Action</th>
    </tr>
    </thead>
</table>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Position</h5>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="delete-text"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Remove</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    let selectedId = null;

    $(document).ready(function () {
        const table = $('#positions-table').DataTable({
            processing: true,
            serverSide: true,
            searchDelay: 2000,
            ajax: {
                url: '{{ route('position.table') }}'
            },
            columns: [
                { data: 'name' },
                { data: 'updated_at' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });

        $('#positions-table tbody').on('click', '.delete-btn', function (e) {
            e.preventDefault();

            const rowData = table.row($(this).closest('tr')).data();

            selectedId = rowData.id;
            const nameToDelete = rowData.name;

            $('#delete-text').text(`Are you sure you want to remove position "${nameToDelete}"?`);
            $('#deleteModal').modal('show');
        });

        $('#confirm-delete').on('click', function () {
            if (!selectedId) return;

            const form = document.getElementById('delete-form');
            form.action = `/positions/remove/${selectedId}`;
            form.submit();
        });

        $('#positions-table tbody').on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            window.location.href = `/positions/edit/${id}`;
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            const success = document.getElementById('session-success');
            const error = document.getElementById('session-error');

            if (success) {
                success.style.transition = 'opacity 0.5s ease';
                success.style.opacity = '0';
                setTimeout(() => success.remove(), 500);
            }

            if (error) {
                error.style.transition = 'opacity 0.5s ease';
                error.style.opacity = '0';
                setTimeout(() => error.remove(), 500);
            }
        }, 5000);
    });
</script>
@endsection
