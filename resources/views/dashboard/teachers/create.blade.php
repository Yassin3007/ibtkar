@extends('dashboard.layouts.master')

@section('content')
    <div class="app-content content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-xs-12 mb-1">
                    <h2 class="content-header-title">{{ __('dashboard.teacher.create') }}</h2>
                </div>
                <div class="content-header-right breadcrumbs-right breadcrumbs-top col-md-6 col-xs-12">
                    <div class="breadcrumb-wrapper col-xs-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('dashboard.common.dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('teachers.index') }}">{{ __('dashboard.teacher.management') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ __('dashboard.teacher.create') }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-form-layouts">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title" id="basic-layout-tooltip">{{ __('dashboard.teacher.create_new') }}</h4>
                                    <a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                                            <li><a data-action="reload"><i class="icon-reload"></i></a></li>
                                            <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                                            <li><a data-action="close"><i class="icon-cross2"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body collapse in">
                                    <div class="card-block">
                                        <div class="card-text">
                                            <p>{{ __('dashboard.teacher.fill_required') }}</p>
                                        </div>

                                        <form class="form" method="POST" action="{{ route('teachers.store') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label for="name">{{ __("dashboard.teacher.fields.name") }}</label>
                                                    <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                                           name="name" value="{{ isset($teacher) ? $teacher->name : old('name') }}"
                                                           placeholder="{{ __("dashboard.teacher.fields.name") }}" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.name") }}">
                                                    @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="phone">{{ __("dashboard.teacher.fields.phone") }}</label>
                                                    <input type="text" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                                           name="phone" value="{{ isset($teacher) ? $teacher->phone : old('phone') }}"
                                                           placeholder="{{ __("dashboard.teacher.fields.phone") }}" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.phone") }}">
                                                    @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="other_phone">{{ __("dashboard.teacher.fields.other_phone") }}</label>
                                                    <input type="text" id="other_phone" class="form-control @error('other_phone') is-invalid @enderror"
                                                           name="other_phone" value="{{ isset($teacher) ? $teacher->other_phone : old('other_phone') }}"
                                                           placeholder="{{ __("dashboard.teacher.fields.other_phone") }}" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.other_phone") }}">
                                                    @error('other_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="bio">{{ __("dashboard.teacher.fields.bio") }}</label>
                                                    <textarea id="bio" rows="5" class="form-control @error('bio') is-invalid @enderror"
                                                              name="bio" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.bio") }}">{{ isset($teacher) ? $teacher->bio : old('bio') }}</textarea>
                                                    @error('bio')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="image">{{ __("dashboard.teacher.fields.image") }}</label>
                                                    <input type="file" id="image" class="form-control @error('image') is-invalid @enderror"
                                                           name="image" value="{{ isset($teacher) ? $teacher->image : old('image') }}"
                                                           placeholder="{{ __("dashboard.teacher.fields.image") }}" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.image") }}">
                                                    @error('image')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="rate">{{ __("dashboard.teacher.fields.rate") }}</label>
                                                    <input type="number" step="0.01" id="rate" class="form-control @error('rate') is-invalid @enderror"
                                                           name="rate" value="{{ isset($teacher) ? $teacher->rate : old('rate') }}"
                                                           data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.rate") }}">
                                                    @error('rate')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="subjects">{{ __("dashboard.teacher.fields.subjects") }}</label>
                                                    <select multiple id="subjects" class="form-control @error('subjects') is-invalid @enderror select2"
                                                            name="subjects[]" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.subjects") }}">
                                                        @foreach($subjects as $subject)
                                                            <option value="{{ $subject->id }}" {{ isset($teacher) && $teacher->subjects->contains($subject->id) ? 'selected' : '' }}>
                                                                {{ $subject->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('subjects')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="stages">{{ __("dashboard.teacher.fields.stages") }}</label>
                                                    <select multiple id="stages" class="form-control @error('stages') is-invalid @enderror select2"
                                                            name="stages[]" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.stages") }}">
                                                        @foreach($stages as $stage)
                                                            <option value="{{ $stage->id }}" {{ isset($teacher) && $teacher->stages->contains($stage->id) ? 'selected' : '' }}>
                                                                {{ $stage->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('stages')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="grades">{{ __("dashboard.teacher.fields.grades") }}</label>
                                                    <select multiple id="grades" class="form-control @error('grades') is-invalid @enderror select2"
                                                            name="grades[]" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.grades") }}">
                                                        @foreach($grades as $grade)
                                                            <option value="{{ $grade->id }}" {{ isset($teacher) && $teacher->grades->contains($grade->id) ? 'selected' : '' }}>
                                                                {{ $grade->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('grades')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="divisions">{{ __("dashboard.teacher.fields.divisions") }}</label>
                                                    <select multiple id="divisions" class="form-control @error('divisions') is-invalid @enderror select2"
                                                            name="divisions[]" data-toggle="tooltip" data-trigger="hover" data-placement="top" data-title="{{ __("dashboard.teacher.fields.divisions") }}">
                                                        @foreach($divisions as $division)
                                                            <option value="{{ $division->id }}" {{ isset($teacher) && $teacher->divisions->contains($division->id) ? 'selected' : '' }}>
                                                                {{ $division->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('divisions')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-actions">
                                                <a href="{{ route('teachers.index') }}" class="btn btn-warning mr-1">
                                                    <i class="icon-cross2"></i> {{ __('dashboard.common.cancel') }}
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="icon-check2"></i> {{ __('dashboard.common.save') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection


@push('page_scripts')
    <script>
        $(document).ready(function() {
            console.log('test uyassin');
            $('#grades').select2();
        });
    </script>
@endpush
