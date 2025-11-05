@extends('layouts.app')

@section('title', 'Create User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'users.index') }}">Users</a>
    </li>
    <li class="breadcrumb-item active"><span>Create User</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Create User</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form class="needs-validation" novalidate action="{{ route('users.store') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <fieldset class="a">
                                <legend class="fs-6 a">Account</legend>
                                {{-- Email Field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-envelope-open"></i>
                                    </span>
                                    <input class="form-control @error('email') is-invalid @enderror" name="email"
                                        type="text" placeholder="Email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Username Field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-user"></i>
                                    </span>
                                    <input class="form-control @error('username') is-invalid @enderror" name="username"
                                        type="text" placeholder="Username" value="{{ old('username') }}"
                                        pattern="^[a-z0-9_]+$" autocomplete="off">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Password field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-lock-locked"></i>
                                    </span>
                                    <input class="form-control @error('password') is-invalid @enderror" type="password"
                                        name="password" placeholder="Password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Repeat Password field --}}
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <i class="cil-lock-locked"></i>
                                    </span>
                                    <input class="form-control @error('repeat_password') is-invalid @enderror"
                                        type="password" name="repeat_password" placeholder="Repeat password">
                                    @error('repeat_password')
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
                                        name="given_name" placeholder="Given Name" value="{{ old('given_name') }}">
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
                                        name="family_name" placeholder="Family Name" value="{{ old('family_name') }}">
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
                                        <option value="" disabled selected>honorif</option>
                                        <option value="Mr.">Mr.</option>
                                        <option value="Mrs.">Mrs.</option>
                                        <option value="Ms.">Ms.</option>
                                        <option value="Miss">Miss</option>
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
                                        value="{{ old('institution_name') }}">
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
                                            <option value="{{ $country->country_id }}">{{ $country->country_name }}
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
                                                {{ old('phone_1_country_code') == $country->phonecode ? 'selected' : '' }}>
                                                {{ $country->country_name . ' ' . '+' . $country->phonecode }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input class="form-control @error('phone_1') is-invalid @enderror" name="phone_1"
                                        id="phone_1" type="text" placeholder="Phone Number 1"
                                        value="{{ old('phone_1') }}">
                                    @error('phone_1')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('phone_1_country_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

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
                                                {{ old('phone_2_country_code') == $country->phonecode ? 'selected' : '' }}>
                                                {{ $country->country_name . ' ' . '+' . $country->phonecode }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input class="form-control @error('phone_2') is-invalid @enderror" type="text"
                                        id="phone_2" name="phone_2" placeholder="Phone Number 2 (Optional)"
                                        value="{{ old('phone_2') }}">
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
