@extends('adminlte::page')

@section('title', 'Employee List')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Employee List</h1>
    <a href="{{ route('employees-list.create') }}" class="btn btn-dark">
        Add Employee
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




<table id="employees-table" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Photo</th>
        <th>Full Name</th>
        <th>Position</th>
        <th>Employment Date</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Salary</th>
        <th>Action</th>
    </tr>
    </thead>
</table>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>


<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Employee</h5>
                <button type="button" class="close"></button>
            </div>
            <div class="modal-body">
                <p id="delete-text"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
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
        const table = $('#employees-table').DataTable({
            processing: true,
            serverSide: true,
            searchDelay: 2000,
            ajax: {
                url: '{{ route('employee.table') }}'
            },
            columns: [
                { data: 'image_path' },
                { data: 'employee_name' },
                { data: 'position_name' },
                { data: 'employment_date' },
                { data: 'phone' },
                { data: 'email' },
                { data: 'salary' },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $('#employees-table tbody').on('click', '.delete-btn', function (deleteF) {
            deleteF.preventDefault();

            const rowData = table.row($(this).closest('tr')).data();

            selectedId = rowData.id;
            const nameToDelete = rowData.employee_name;

            $('#delete-text').text(`Are you sure you want to delete the employee "${nameToDelete}"?`);
            $('#deleteModal').modal('show');
        });

        $('#confirm-delete').on('click', function () {
            if (!selectedId) return;

            const form = document.getElementById('delete-form');
            form.action = `/employees/remove/${selectedId}`;
            form.submit();
        });


        $('#employees-table tbody').on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            window.location.href = `/employees/edit/${id}`;
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
