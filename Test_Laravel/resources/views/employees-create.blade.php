@extends('adminlte::page')

@section('title', 'Add Employee')

@section('content_header')
<h1>Employees</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong class="mb-0">Add employee</strong>
    </div>

    <div class="card-body">
        <form action="{{ route('employees.create') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id') }}">
            <input type="hidden" name="supervisor_id" id="supervisor_id" value="{{ old('supervisor_id') }}">
            <input type="hidden" name="supervisor_user_id" id="supervisor_user_id" value="{{ old('supervisor_user_id') }}">

            <div class="form-group">
                <label for="image_path">Photo</label>
                <input type="file" id="image_path" class="form-control-file" accept="image/*">
                <input type="hidden" name="processed_image_path" id="processed_image_path">

                <div class="mt-2" id="photo-preview-container" style="display:none;">
                    <img id="photo-preview" alt="Preview" style="max-width: 300px;" />
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="remove-photo-btn">
                        Remove photo
                    </button>
                </div>

                <small class="form-text text-muted">
                    File format jpg,png up to 5MB, the minimum size of 300x300px
                </small>
                <span class="invalid-feedback d-block" id="image_path_error" style="display: none;"></span>
            </div>

            <div class="form-group position-relative">
                <label for="employee_name">Name</label>
                <input type="text" name="employee_name" id="employee_name"
                       class="form-control @error('employee_name') is-invalid @enderror @error('user_id') is-invalid @enderror"
                       value="{{ old('employee_name') }}" autocomplete="off">
                <small class="text-muted d-block text-right" id="name-count">0 / 256</small>
                <div id="search-suggestions" class="list-group mt-1 position-absolute w-100" style="z-index: 1000;"></div>

                @error('employee_name')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                @error('user_id')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="+380 (xx) XXX XX XX">
                @error('phone')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}">
                @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="position_id">Position</label>
                <select name="position_id" id="position_id" class="form-control"></select>
                <small id="loading-positions" style="display:none;">Loading...</small>
                <button type="button" class="btn btn-sm btn-link mt-2" id="load-more-positions" style="display: none;">
                    Load more positions
                </button>
                @error('position_id')
                <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="salary">Salary $</label>
                <input type="text" name="salary" id="salary"
                       class="form-control @error('salary') is-invalid @enderror"
                       value="{{ old('salary') }}">
                @error('salary')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group position-relative">
                <label for="head">Head</label>
                <input type="text" name="head" id="head"
                       class="form-control @error('head') is-invalid @enderror @error('supervisor_id') is-invalid @enderror"
                       value="{{ old('head') }}" autocomplete="off">
                <div id="head-suggestions" class="list-group mt-1 position-absolute w-100" style="z-index: 1000;"></div>
                @error('supervisor_id')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="employment_date">Date of employment</label>
                <input type="date" name="employment_date" id="employment_date"
                       class="form-control @error('employment_date') is-invalid @enderror"
                       value="{{ old('employment_date') }}">
                @error('employment_date')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('employees-list') }}" class="btn btn-outline-dark">Cancel</a>
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    let selectedUser = {
        user_id: null,
        supervisor_id: null,
    };

    $('#employee_name').on('input', function () {
        $('#name-count').text(`${this.value.length} / 256`);
    }).trigger('input');

    // $(document).ready(function () {
    //     $.ajax({
    //         url: '{{ route('positions.get') }}',
    //         method: 'GET',
    //         headers: {
    //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //         },
    //         success: function (data) {
    //             const select = $('#position_id');
    //             select.empty().append('<option value="">Select a position</option>');
    //             data.forEach(function (position) {
    //                 const option = $('<option>', {
    //                     value: position.id,
    //                     text: position.name
    //                 });
    //                 if ('{{ old('position_id') }}' === position.id) {
    //                     option.prop('selected', true);
    //                 }
    //                 select.append(option);
    //             });
    //         },
    //         error: function () {
    //             $('#position_id').empty().append('<option value="">Loading error</option>');
    //         }
    //     });
    // });

    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;

    function loadPositions() {
        if (isLoading || !hasMore) return;

        isLoading = true;
        $('#loading-positions').show();

        $.get('{{ route('positions.get') }}', { page: currentPage }, function (response) {
            response.data.forEach(position => {
                $('#position_id').append(
                    $('<option>', {
                        value: position.id,
                        text: position.name
                    })
                );
            });

            hasMore = response.current_page < response.last_page;
            currentPage++;
            $('#loading-positions').hide();
            $('#load-more-positions').toggle(hasMore);
            isLoading = false;
        });
    }

    $(document).ready(function () {
        $('#position_id').empty().append('<option value="">Select a position</option>');
        loadPositions(); // initial

        $('#load-more-positions').on('click', function () {
            loadPositions();
        });
    });


    let nameSearchTimeout = null;
    let headSearchTimeout = null;

    $('#employee_name').on('input', function () {
        clearTimeout(nameSearchTimeout);

        const query = $(this).val();
        const suggestions = $('#search-suggestions');

        if (query.length < 2) {
            suggestions.empty().hide();
            return;
        }

        nameSearchTimeout = setTimeout(function () {
            $.ajax({
                url: '{{ route('search') }}',
                method: 'GET',
                data: { name: query },
                success: function (data) {
                    suggestions.empty();

                    if (!data.length) {
                        suggestions.hide();
                        return;
                    }

                    data.forEach(function (item) {
                        const displayName = item.name ?? '';
                        suggestions.append(`
                        <button type="button" class="list-group-item list-group-item-action"
                            data-name="${displayName}"
                            data-email="${item.email ?? ''}"
                            data-phone="${item.phone ?? ''}"
                            data-user_id="${item.user_id ?? null}">
                            ${displayName}
                        </button>
                    `);
                    });

                    suggestions.show();

                    const first = data[0];
                    if (
                        first &&
                        data.length === 1 &&
                        first.name?.toLowerCase() === query.toLowerCase()
                    ) {
                        const email = first.email ?? '';
                        const phone = first.phone ?? '';
                        const user_id = first.user_id ?? null;

                        $('#email').val(email);
                        $('#phone').val(phone);
                        $('#user_id').val(user_id);

                        suggestions.empty().hide();
                    }
                },
                error: function () {
                    suggestions.empty().hide();
                }
            });
        }, 2000);
    });

    $('#head').on('input', function () {
        clearTimeout(headSearchTimeout);

        const query = $(this).val();
        const suggestions = $('#head-suggestions');

        if (query.length < 2) {
            suggestions.empty().hide();
            return;
        }

        headSearchTimeout = setTimeout(function () {
            $.ajax({
                url: '{{ route('search') }}',
                method: 'GET',
                data: { employee_name: query },
                success: function (data) {
                    suggestions.empty();

                    if (!data.length) {
                        suggestions.hide();
                        return;
                    }

                    data.forEach(function (item) {
                        const displayName = item.employee_name ?? '';
                        suggestions.append(`
                        <button type="button" class="list-group-item list-group-item-action"
                            data-name="${displayName}"
                            data-user_id="${item.supervisor_user_id ?? null}"
                            data-id="${item.supervisor_id ?? null}">
                            ${displayName}
                        </button>
                    `);
                    });

                    suggestions.show();

                    const first = data[0];
                    if (
                        first &&
                        data.length === 1 &&
                        first.employee_name?.toLowerCase() === query.toLowerCase()
                    ) {
                        $('#supervisor_id').val(first.id ?? null);
                        $('#supervisor_user_id').val(first.user_id ?? null);

                        suggestions.empty().hide();
                    }
                },
                error: function () {
                    suggestions.empty().hide();
                }
            });
        }, 2000);
    });

    $(document).on('click', '#search-suggestions .list-group-item', function () {
        const user_id = $(this).data('user_id') ?? null;
        const employee_name = $(this).data('name') ?? '';
        const email = $(this).data('email') ?? '';
        const phone = $(this).data('phone') ?? '';

        $('#employee_name').val(employee_name);
        $('#email').val(email);
        $('#phone').val(phone);

        $('#user_id').val(user_id);

        $('#name-count').text(`${employee_name.length} / 256`);

        $('#search-suggestions').empty().hide();
    });


    $(document).on('click', '#head-suggestions .list-group-item', function () {
        const name = $(this).data('name') ?? '';
        const id = $(this).data('id') ?? null;
        const user_id = $(this).data('user_id') ?? null;

        $('#supervisor_id').val(id);
        $('#supervisor_user_id').val(user_id);
        $('#head').val(name);

        $('#head-suggestions').empty().hide();
    });

    document.getElementById('image_path').addEventListener('change', async function () {
        const file = this.files[0];
        const imageInput = this;
        const imageError = document.getElementById('image_path_error');
        const preview = document.getElementById('photo-preview');
        const container = document.getElementById('photo-preview-container');
        const hiddenInput = document.getElementById('processed_image_path');

        imageError.innerText = '';
        imageError.style.display = 'none';
        imageInput.classList.remove('is-invalid');

        if (!file) return;

        const formData = new FormData();
        formData.append('image_path', file);

        try {
            const response = await fetch('{{ route('employee.image') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                let message = 'Upload failed';

                if (data.errors && data.errors.image_path) {
                    message = data.errors.image_path;
                } else if (data.message) {
                    message = data.message;
                }

                imageError.innerText = message;
                imageError.style.display = 'block';
                imageInput.classList.add('is-invalid');
                return;
            }

            if (data.image_path) {
                preview.src = `/storage/${data.image_path}`;
                container.style.display = 'block';
                hiddenInput.value = data.image_path;
            }

        } catch (e) {
            imageError.innerText = 'Error';
            imageError.style.display = 'block';
            imageInput.classList.add('is-invalid');
        }
    });

    document.getElementById('remove-photo-btn').onclick = async () => {
        const imageInput = document.getElementById('image_path');
        const imageError = document.getElementById('image_path_error');
        const preview = document.getElementById('photo-preview');
        const container = document.getElementById('photo-preview-container');
        const hiddenInput = document.getElementById('processed_image_path');
        const image_path = hiddenInput.value;

        imageError.innerText = '';
        imageError.style.display = 'none';
        imageInput.classList.remove('is-invalid');

        if (!image_path) {
            imageInput.value = '';
            preview.src = '';
            container.style.display = 'none';
            return;
        }

        try {
            const response = await fetch('{{ route('employee.remove_image') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ image_path })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const message = data?.error || 'Error';
                imageError.innerText = message;
                imageError.style.display = 'block';
                imageInput.classList.add('is-invalid');
            }

            imageInput.value = '';
            preview.src = '';
            container.style.display = 'none';
            hiddenInput.value = '';

        } catch (e) {
            imageError.innerText = 'Error';
            imageError.style.display = 'block';
            imageInput.classList.add('is-invalid');
        }
    };
    document.getElementById('image_path').addEventListener('click', function (e) {
        const currentImagePath = document.getElementById('processed_image_path').value;
        const imageError = document.getElementById('image_path_error');

        imageError.innerText = '';
        imageError.style.display = 'none';

        if (currentImagePath) {
            e.preventDefault();
            imageError.innerText = 'You must remove the existing photo before uploading a new one.';
            imageError.style.display = 'block';
        }
    });
</script>
@endsection

