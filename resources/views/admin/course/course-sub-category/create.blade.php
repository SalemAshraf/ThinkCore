@extends('admin.layouts.master')
@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add new category</h3>
                    <div class="card-actions">
                        <a href="{{ route('admin.course-categories.create') }}" class="btn btn-primary">
                            <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M12 5l0 14"></path>
                                <path d="M5 12l14 0"></path>
                            </svg>
                            Add New Category
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.course-sub-categories.store', $courseCategory->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <x-input name="name" label="Category Name" placeholder="Enter category name" />
                            </div>
                            <div class="col-md-6">
                                <div class="form-label">Category Image</div>
                                <input type="file" name="image" class="form-control" placeholder="Enter category image URL">
                            </div>
                            <div class="col-md-6">
                                <div class="form-label">Category Icon</div>
                                <input type="file" name="icon" class="form-control" placeholder="Enter category icon URL">
                            </div>
                            <div class="col-md-6" style="margin-top: 16px">
                                <div class="form-label">Is Trending</div>
                                <label class="form-check form-switch form-switch-2">
                                    <input type="hidden" name="is_trending" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_trending" value="1">
                                    <span class="form-check-label">Make it in Most Trending Section</span>
                                </label>
                            </div>
                            <div class="col-md-6" style="margin-top: 16px">
                                <div class="form-label">Category Status</div>
                                <label class="form-check form-switch form-switch-2">
                                    <input type="hidden" name="status" value="0">
                                    <input class="form-check-input" type="checkbox" name="status" value="1">
                                    <span class="form-check-label">Make it Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-device-floppy">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                    <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                    <path d="M14 4l0 4l-6 0l0 -4" />
                                </svg>
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection
