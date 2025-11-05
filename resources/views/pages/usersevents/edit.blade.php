@extends('layouts.app')

@section('title', 'Edit Roole')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route('events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><span>Edit Role</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Role Event {{ $user->event->event_name }}</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form class="needs-validation" novalidate
                        action="{{ route('users.events.update', [$user->event->event_code, $user->user->username]) }}"
                        method="post">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <fieldset>
                                <legend>User</legend>
                                <div class="mb-3">
                                    <label for="given_name" class="form-label">Given Name</label>
                                    <input type="text" class="form-control @error('given_name') is-invalid @enderror"
                                        id="given_name" name="given_name" required disabled
                                        value="{{ old('given_name', $user->user->given_name) }}">
                                    @error('given_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="family_name" class="form-label">Family Name</label>
                                    <input type="text" class="form-control @error('family_name') is-invalid @enderror"
                                        id="family_name" name="family_name" required disabled
                                        value="{{ old('family_name', $user->user->family_name) }}">
                                    @error('family_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="institution" class="form-label">Institution</label>
                                    <input type="text" class="form-control @error('institution') is-invalid @enderror"
                                        id="institution" name="institution" required disabled
                                        value="{{ old('institution', $user->user->institution_name) }}">
                                    @error('institution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror"
                                        id="country" name="country" required disabled
                                        value="{{ old('country', $user->user->country->country_name) }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend>Roles</legend>
                                <div class="mb-3">
                                    <label class="form-label">Roles</label>
                                    <div class="form-check-group">
                                        @foreach ($roles as $role)
                                            <div class="form-check">
                                                <input type="checkbox"
                                                    class="form-check-input @error('roles') is-invalid @enderror"
                                                    id="role_{{ $role->role_id }}" name="roles[]"
                                                    value="{{ $role->role_id }}"
                                                    @if (in_array($role->role_id, old('roles', $user->roles))) checked @endif>
                                                <label class="form-check-label" for="role_{{ $role->role_id }}">
                                                    {{ $role->role_name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('roles')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>
                            @if ($user_expertise)
                                <fieldset class="mb-4">
                                    <legend class="fs-6">Expertise</legend>
                                    <div class="mb-3">
                                        {{-- <label for="keywords" class="form-label">Keywords<span
                                                class="text-danger">*</span></label> --}}
                                        <input type="text" class="form-control @error('expertise') is-invalid @enderror"
                                            id="expertise" name="expertise" disabled
                                            value="{{ old('expertise', $user_expertise) }}" placeholder="Expertise">
                                        @error('expertise')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="topics" class="form-label">Review Topics<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('topics') is-invalid @enderror"
                                            id="topics" name="topics" value="{{ old('topics', $user->topics) }}"
                                            placeholder="Review Topics">
                                        @error('topics')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-danger">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <script>
        var inputExpertise = null;
        var inputTopics = null;

        if (document.querySelector('input[name=expertise]')) {
            inputExpertise = document.querySelector('input[name=expertise]');
        }

        if (document.querySelector('input[name=topics]')) {
            inputTopics = document.querySelector('input[name=topics]');
        }

        if (inputTopics) {
            new Tagify(inputTopics, {
                enforceWhitelist: true,
                tagTextProp: 'name',
                whitelist: [
                    @if ($topics)
                        @foreach ($topics as $topic)
                            {
                                value: "{{ $topic->topic_id }}",
                                name: "{{ $topic->topic_name }}"
                            },
                        @endforeach
                    @endif
                ],
                dropdown: {
                    enabled: 0,
                    // position: 'text', 
                    mapValueTo: 'name',
                    highlightFirst: true,
                    searchKeys: ['name']
                }
            });
        }

        if (inputExpertise) {
            new Tagify(inputExpertise)
        }
    </script>
    
@endsection
