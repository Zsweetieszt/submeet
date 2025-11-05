@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'users.index') }}">Users</a>
    </li>
    <li class="breadcrumb-item active"><span>Edit User</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit User</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form class="needs-validation" novalidate action="{{ route('users.update', $user->username) }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <fieldset class="a">
                                <legend class="fs-6 a">Account</legend>
                                {{-- Email Field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-envelope-open"></i>
                                    </span>
                                    <input disabled class="form-control @error('email') is-invalid @enderror" name="email"
                                        type="text" placeholder="Email" value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Username Field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-user"></i>
                                    </span>
                                    <input class="form-control" name="username" type="text" placeholder="Username"
                                        value="{{ old('username', $user->username) }}" readonly>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend class="fs-6">Identity</legend>
                                {{-- Given Name field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-user"></i>
                                    </span>
                                    <input class="form-control @error('given_name') is-invalid @enderror" type="text"
                                        name="given_name" placeholder="Given Name"
                                        value="{{ old('given_name', $user->given_name) }}">
                                    @error('given_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Family Name field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-user"></i>
                                    </span>
                                    <input class="form-control @error('family_name') is-invalid @enderror" type="text"
                                        name="family_name" placeholder="Family Name"
                                        value="{{ old('family_name', $user->family_name) }}">
                                    @error('family_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- honorif field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-user"></i>
                                    </span>
                                    <select class="form-control form-select @error('honorif') is-invalid @enderror" name="honorif"
                                        value="{{ old('honorif') }}">
                                        <option value="Mr." {{ $user->honorif == 'Mr.' ? 'selected' : '' }}>Mr.
                                        </option>
                                        <option value="Mrs." {{ $user->honorif == 'Mrs.' ? 'selected' : '' }}>Mrs.
                                        </option>
                                        <option value="Ms." {{ $user->honorif == 'Ms.' ? 'selected' : '' }}>Ms.
                                        </option>
                                        <option value="Miss" {{ $user->honorif == 'Miss.' ? 'selected' : '' }}>Miss
                                        </option>
                                    </select>
                                    @error('honorif')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend class="fs-6">Institution</legend>
                                {{-- Institution Name field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-institution"></i>
                                    </span>
                                    <input class="form-control @error('institution_name') is-invalid @enderror"
                                        type="text" name="institution_name" placeholder="Institution Name"
                                        value="{{ old('institution_name', $user->institution_name) }}">
                                    @error('institution_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Country field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-globe-alt"></i>
                                    </span>
                                    <select class="form-control form-select @error('country') is-invalid @enderror" name="country"
                                        value="{{ old('country') }}">
                                        <option value="" disabled selected>Country</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->country_id }}"
                                                {{ $user->country_id == $country->country_id ? 'selected' : '' }}>
                                                {{ $country->country_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>
                            <fieldset class="mb-4">
                                <legend class="fs-6">Contact</legend>
                                {{-- Phone Number 1 field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-phone"></i>
                                    </span>
                                    <select class="form-select" name="phone_1_country_code" id="phone_1_country_code"
                                        style="max-width: 110px;">
                                        <option value="" selected disabled>Select Country Code</option>
                                        @foreach ($countries as $country)
                                            <option data-label="{{ $country->country_name . ' +' . $country->phonecode }}"
                                                value="{{ $country->phonecode }}"
                                                {{ old('phone_1_country_code', $user->ct_phone_number_1) == $country->phonecode ? 'selected' : '' }}>
                                                {{ $country->country_name . ' ' . '+' . $country->phonecode }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input class="form-control @error('phone_1') is-invalid @enderror" name="phone_1"
                                        id="phone_1" type="text" placeholder="Phone Number 1"
                                        value="{{ old('phone_1', $user->phone_number_1) }}">
                                    @error('phone_1')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('phone_1_country_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- {{$user}} --}}
                                {{-- Phone Number 2 field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-phone"></i>
                                    </span>
                                    <select class="form-select" name="phone_2_country_code" id="phone_2_country_code"
                                        style="max-width: 110px;">
                                        <option value="" selected disabled>Select Country Code</option>
                                        @foreach ($countries as $country)
                                            <option data-label="{{ $country->country_name . ' +' . $country->phonecode }}"
                                                value="{{ $country->phonecode }}"
                                                {{ old('phone_2_country_code', $user->ct_phone_number_2) == $country->phonecode ? 'selected' : '' }}>
                                                {{ $country->country_name . ' ' . '+' . $country->phonecode }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input class="form-control @error('phone_2') is-invalid @enderror" type="text"
                                        id="phone_2" name="phone_2" placeholder="Phone Number 2 (Optional)"
                                        value="{{ old('phone_2', $user->phone_number_2) }}">
                                    @error('phone_2')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('phone_2_country_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-danger">Cancel</a>
                            <button type="submit" class="btn btn-primary confirm-submit">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".confirm-submit").forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    let form = this.closest("form");

                    Swal.fire({
                        title: "Are you sure want to submit?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        cancelButtonText: "No, cancel!",
                        confirmButtonText: "Yes, submit!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".confirm-submit").forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    let form = this.closest("form");

                    Swal.fire({
                        title: "Are you sure want to submit?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        cancelButtonText: "No, cancel!",
                        confirmButtonText: "Yes, submit!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });

        function handleCountrySelect(selectId) {
            const select = document.getElementById(selectId);
            if (!select) return;

            const options = select.options;

            // Restore full label for all options
            for (let option of options) {
                const fullLabel = option.getAttribute('data-label');
                if (fullLabel) {
                    option.textContent = fullLabel;
                }
            }

            // Shorten only the selected one
            const selectedOption = select.options[select.selectedIndex];
            selectedOption.textContent = '+' + selectedOption.value;

            // When dropdown is opened, restore all labels for full list
            select.addEventListener('mousedown', () => {
                for (let option of options) {
                    const fullLabel = option.getAttribute('data-label');
                    if (fullLabel) {
                        option.textContent = fullLabel;
                    }
                }
            });

            // When changed, reapply short label to selected only
            select.addEventListener('change', () => {
                handleCountrySelect(selectId);
            });
        }

        // Initialize on page load
        handleCountrySelect('phone_1_country_code');
        handleCountrySelect('phone_2_country_code');
    </script>
    
@endsection
