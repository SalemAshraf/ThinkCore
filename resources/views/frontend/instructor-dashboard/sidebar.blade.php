<div class="col-xl-3 col-md-4 wow fadeInLeft">
                    <div class="wsus__dashboard_sidebar">
                        <div class="wsus__dashboard_sidebar_top">
                            <div class="dashboard_banner">
                                <img src="{{ asset('frontend/assets/images/single_topic_sidebar_banner.jpg') }}" alt="img"
                                    class="img-fluid">
                            </div>
                            <div class="img">
                                <img src="{{ asset(Auth::user()->image) }}" alt="profile"
                                    class="img-fluid w-100">
                            </div>
                            <h4>{{ Auth::user()->name }}</h4>
                            <p>{{ Auth::user()->role }}</p>
                        </div>
                        <ul class="wsus__dashboard_sidebar_menu">
                            <li>
                                <a href="{{ route('instructor.dashboard') }}" class="{{ (isset($active) && $active == 'dashboard') ? 'active' : '' }}">
                                    <div class="img">
                                        <img src="{{ asset('frontend/assets/images/dash_icon_8.png') }}" alt="icon"
                                            class="img-fluid w-100">
                                    </div>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('instructor.profile') }}" class="{{ (isset($active) && $active == 'profile') ? 'active' : '' }}">
                                    <div class="img">
                                        <img src="{{ asset('frontend/assets/images/dash_icon_1.png') }}" alt="icon"
                                            class="img-fluid w-100">
                                    </div>
                                    Profile
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <div class="img">
                                        <img src="{{ asset('frontend/assets/images/dash_icon_16.png') }}" alt="icon"
                                            class="img-fluid w-100">
                                    </div>
                                    Logout
                                </a>
                                <form method="POST" id="logout-form" action="{{ route('logout') }}">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
