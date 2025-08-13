@extends('frontend.instructor-dashboard.course.course-app')
@section('course-content')
    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
        <div class="add_course_basic_info">
            <form action="{{ route('instructor.courses.store.basic_info') }}" method="POST" enctype="multipart/form-data"
                class="basic-info-update-form course-form">
                @csrf
                <input type="hidden" name="id" value="{{ $course->id }}">
                <input type="hidden" name="current_step" value="1">
                <input type="hidden" name="next_step" value="2">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="add_course_basic_info_imput">
                            <label for="#">Title *</label>
                            <input type="text" placeholder="Title" name="title" value="{{ $course->title }}" required>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="add_course_basic_info_imput">
                            <label for="#">Seo description</label>
                            <input type="text" placeholder="Seo description" value="{{ $course->seo_description }}"
                                name="seo_description">
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="add_course_basic_info_imput">
                            <label for="#">Thumbnail *</label>
                            <input type="file" value="{{ $course->thumbnail }}" name="thumbnail">
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="add_course_basic_info_imput">
                            <label for="#">Demo Video Storage <b>(optional)</b></label>
                            <select class="form-select storage" style="padding: 12px 20px;" name="demo_video">
                                <option value=""> Please Select </option>
                                <option value="upload" {{ old('demo_video', $course->demo_video ?? '') == 'upload' ? 'selected' : '' }}> Upload </option>
                                <option value="youtube" {{ old('demo_video', $course->demo_video ?? '') == 'youtube' ? 'selected' : '' }}> Youtube </option>
                                <option value="vimeo" {{ old('demo_video', $course->demo_video ?? '') == 'vimeo' ? 'selected' : '' }}> Vimeo </option>
                                <option value="external_link" {{ old('demo_video', $course->demo_video ?? '') == 'external_link' ? 'selected' : '' }}> External Link </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div
                            class="add_course_basic_info_imput upload_source {{ old('demo_video', $course->demo_video ?? '') == 'upload' ? '' : 'd-none' }}">
                            <label for="#">Path</label>
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                        <i class="fa fa-picture-o"></i> Choose
                                    </a>
                                </span>
                                <input id="thumbnail" class="form-control source_input" type="text" name="file"
                                    value="{{ old('file', $course->demo_video_source ?? '') }}">
                            </div>
                        </div>

                        <div
                            class="add_course_basic_info_imput external_source {{ in_array(old('demo_video', $course->demo_video ?? ''), ['youtube', 'vimeo', 'external_link']) ? '' : 'd-none' }}">
                            <label for="#">Path</label>
                            <input type="text" name="url" class="source_input" value="{{ old('url', $course->demo_video_source ?? '') }}">
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="add_course_basic_info_imput">
                            <label for="#">Price *</label>
                            <input type="text" placeholder="Price" name="price" value="{{ $course->price }}" required>
                            <p>Put 0 for free</p>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="add_course_basic_info_imput">
                            <label for="#">Discount Price</label>
                            <input type="text" placeholder="Price" value="{{ $course->discount }}" name="discount">
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="add_course_basic_info_imput mb-0">
                            <label for="#">Description</label>
                            <textarea rows="8" placeholder="Description"
                                name="description">{{ old('description', $course->description ?? '') }}</textarea>
                            <button type="submit" class="common_btn mt_20">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $('#lfm').filemanager('file');
    </script>
@endpush
