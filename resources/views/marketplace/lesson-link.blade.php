<a class="row" href="{{ route('marketplace.lesson', [$course->slug, $lesson->id]) }}"><span>{{ $lesson->title }}</span><small>{{ $lesson->is_preview ? 'معاينة مجانية' : 'للمشتركين' }}</small></a>
