@extends('frontend.layouts.master')
@section('content')
    <!--===========================
                                                BREADCRUMB START
                                            ============================-->
    <section class="wsus__breadcrumb" style="background: url({{ asset('frontend/assets/images/breadcrumb_bg.jpg') }});">
        <div class="wsus__breadcrumb_overlay">
            <div class="container">
                <div class="row">
                    <div class="col-12 wow fadeInUp">
                        <div class="wsus__breadcrumb_text">
                            <h1>Instructor Dashboard</h1>
                            <ul>
                                <li><a href="{{ url('/') }}">Home</a></li>
                                <li>Instructor Dashboard</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--===========================
                                                BREADCRUMB END
                                            ============================-->


    <!--===========================
                                                DASHBOARD OVERVIEW START
                                            ============================-->
    <section class="wsus__dashboard mt_90 xs_mt_70 pb_120 xs_pb_100">
        <div class="container">
            <div class="row">
                @include('frontend.instructor-dashboard.sidebar', ['active' => 'profile'])
                <div class="col-xl-9 col-md-8 wow fadeInRight" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="wsus__dashboard_contant">
                        <div class="wsus__dashboard_contant_top d-flex flex-wrap justify-content-between">
                            <div class="wsus__dashboard_heading">
                                <h5>Update Your Information</h5>
                                <p>Manage your courses and its update like live, draft and insight.</p>
                            </div>
                            <div class="wsus__dashboard_profile_delete">
                                <a href="#" class="common_btn">Delete Profile</a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('instructor.profile.update') }}" enctype="multipart/form-data" class="wsus__dashboard_profile_update">
                            @csrf
                            <div class="wsus__dashboard_profile wsus__dashboard_profile_avatar">
                                <div class="img">
                                    <img src="{{ asset(Auth::user()->image) }}" alt="profile" class="img-fluid w-100">
                                    <label for="profile_photo">
                                        <img src="{{ asset('frontend/assets/images/dash_camera.png') }}" alt="camera"
                                            class="img-fluid w-100">
                                    </label>
                                    <input type="file" name="avatar" id="profile_photo" hidden="">
                                </div>
                                <div class="text">
                                    <h6>Your avatar</h6>
                                    <p>PNG or JPG no bigger than 400px wide and tall.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Full name</label>
                                        <input type="text" placeholder="Enter your full name" name="name"
                                            value="{{ auth()->user()->name }}">
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Headline</label>
                                        <input type="text" placeholder="Enter your headline" name="headline"
                                            value="{{ auth()->user()->headline }}">
                                        <x-input-error :messages="$errors->get('headline')" class="mt-2" />
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Email</label>
                                        <input type="text" placeholder="Enter your email" name="email"
                                            value="{{ auth()->user()->email }}">
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Gender</label>
                                        <select name="gender" id="" class="form-control" name="gender"
                                            style="padding: 11px; margin-top: 5px;">
                                            <option value="male" {{ auth()->user()->gender === 'male' ? 'selected' : '' }}>
                                                Male</option>
                                            <option value="female" {{ auth()->user()->gender === 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>About Me</label>
                                        <textarea rows="7" placeholder="Your text here"
                                            name="bio">{{ auth()->user()->bio }}</textarea>
                                        <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_btn">
                                        <button type="submit" class="common_btn">Update Profile</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="wsus__dashboard_contant">
                        <div class="wsus__dashboard_contant_top d-flex flex-wrap justify-content-between">
                            <div class="wsus__dashboard_heading">
                                <h5>Update Your Password</h5>
                                <p>Manage your password and its updates.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('instructor.profile.password.update') }}"
                            class="wsus__dashboard_profile_update">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Current Password</label>
                                        <input type="password" placeholder="Enter your current password"
                                            name="current_password" value="{{ old('current_password') }}">
                                    </div>
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>New Password</label>
                                        <input type="password" placeholder="Enter your new password" name="new_password"
                                            value="{{ old('new_password') }}">
                                    </div>
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Confirm Password</label>
                                        <input type="password" placeholder="Confirm your new password"
                                            name="new_password_confirmation" value="{{ old('new_password_confirmation') }}">
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_btn">
                                        <button type="submit" class="common_btn">Update Password</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="wsus__dashboard_contant">
                        <div class="wsus__dashboard_contant_top d-flex flex-wrap justify-content-between">
                            <div class="wsus__dashboard_heading">
                                <h5>Update Your Social Links</h5>
                                <p>Manage your social links and their updates.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('instructor.profile.social.update') }}"
                            class="wsus__dashboard_profile_update">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Facebook Link</label>
                                        <input type="text" placeholder="Enter your Facebook link" name="facebook"
                                            value="{{ auth()->user()->facebook }}">
                                        <x-input-error :messages="$errors->get('facebook')" class="mt-2" />
                                    </div>
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Twitter Link</label>
                                        <input type="text" placeholder="Enter your Twitter link" name="twitter"
                                            value="{{ auth()->user()->twitter }}">
                                        <x-input-error :messages="$errors->get('twitter')" class="mt-2" />
                                    </div>
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>LinkedIn Link</label>
                                        <input type="text" placeholder="Enter your LinkedIn link" name="linkedin"
                                            value="{{ auth()->user()->linkedin }}">
                                        <x-input-error :messages="$errors->get('linkedin')" class="mt-2" />
                                    </div>
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>GitHub Link</label>
                                        <input type="text" placeholder="Enter your GitHub link" name="github"
                                            value="{{ auth()->user()->github }}">
                                        <x-input-error :messages="$errors->get('github')" class="mt-2" />
                                    </div>
                                    <div class="wsus__dashboard_profile_update_info">
                                        <label>Website Link</label>
                                        <input type="text" placeholder="Enter your Website link" name="website"
                                            value="{{ auth()->user()->website }}">
                                        <x-input-error :messages="$errors->get('website')" class="mt-2" />
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="wsus__dashboard_profile_update_btn">
                                        <button type="submit" class="common_btn">Update Links</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--===========================
                                                DASHBOARD OVERVIEW END
                                            ============================-->
@endsection
