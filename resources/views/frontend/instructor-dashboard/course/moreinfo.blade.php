@extends('frontend.instructor-dashboard.course.course-app')
@section('course-content')
    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
        <div class="add_course_basic_info">
            <form action="{{ route('instructor.courses.moreinfo') }}" class="more_info_form course-form" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ request()?->id }}">
                <input type="hidden" name="current_step" value="2">
                <input type="hidden" name="next_step" value="2">

                <div class="row">
                    {{-- Capacity --}}
                    <div class="col-xl-6">
                        <div class="add_course_more_info_input">
                            <label for="#">Capacity</label>
                            <input type="text" placeholder="Capacity" name="capacity"
                                   value="{{ old('capacity', $course?->capacity) }}">
                            <p>leave blank for unlimited</p>
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div class="col-xl-6">
                        <div class="add_course_more_info_input">
                            <label for="#">Course Duration (Minutes)*</label>
                            <input type="text" placeholder="300" name="duration"
                                   value="{{ old('duration', $course?->duration) }}">
                        </div>
                    </div>

                    {{-- Checkboxes --}}
                    <div class="col-xl-6">
                        <div class="add_course_more_info_checkbox">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="qna" value="1" id="flexCheckDefault"
                                    {{ old('qna', $course?->qna) ? 'checked' : '' }}>
                                <label class="form-check-label" for="flexCheckDefault">Q&A</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="certificate" value="1" id="flexCheckDefault2"
                                    {{ old('certificate', $course?->certificate) ? 'checked' : '' }}>
                                <label class="form-check-label" for="flexCheckDefault2">Completion Certificate</label>
                            </div>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div class="col-12">
                        <div class="add_course_more_info_input">
                            <label for="#">Category *</label>
                            <select class="select_2" name="category">
                                <option value=""> Please Select </option>
                                @foreach ($categories as $category)
                                    @if($category->subCategories->isNotEmpty())
                                        <optgroup label="{{ $category->name }}">
                                            @foreach ($category->subCategories as $subCategories)
                                                <option value="{{ $subCategories->id }}"
                                                    {{ old('category', $course?->category_id) == $subCategories->id ? 'selected' : '' }}>
                                                    {{ $subCategories->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Level --}}
                    <div class="col-xl-4">
                        <div class="add_course_more_info_radio_box">
                            <h3>Level</h3>
                            @foreach ($levels as $level)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="level" value="{{ $level->id }}"
                                        id="id-{{ $level->id }}"
                                        {{ old('level', $course?->course_level_id) == $level->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="id-{{ $level->id }}">
                                        {{ $level->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Language --}}
                    <div class="col-xl-4">
                        <div class="add_course_more_info_radio_box">
                            <h3>Language</h3>
                            @foreach ($Languages as $Language)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="language" value="{{ $Language->id }}"
                                        id="id-{{ $Language->id }}"
                                        {{ old('language', $course?->course_language_id) == $Language->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="id-{{ $Language->id }}">
                                        {{ $Language->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="col-xl-12">
                        <button type="submit" class="common_btn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
