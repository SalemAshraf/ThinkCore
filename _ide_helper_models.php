<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Admin
 *
 * @method \Laravel\Sanctum\NewAccessToken createToken(string $name, array $abilities = ['*'])
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $instructor_id
 * @property int|null $category_id
 * @property string $course_type
 * @property string $title
 * @property string $slug
 * @property string|null $seo_description
 * @property string|null $duration
 * @property string|null $time_zone
 * @property string|null $thumbnail
 * @property string|null $demo_video
 * @property string|null $demo_video_source
 * @property string|null $description
 * @property int|null $capacity
 * @property float|null $price
 * @property float|null $discount
 * @property int|null $certificate
 * @property int|null $qna
 * @property string|null $message_for_review
 * @property string $is_approved
 * @property string $status
 * @property int|null $course_level_id
 * @property int|null $course_language_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCertificate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCourseLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCourseLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCourseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDemoVideo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDemoVideoSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereInstructorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereMessageForReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereQna($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereSeoDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereUpdatedAt($value)
 */
	class Course extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property string|null $icon
 * @property int|null $parent_id
 * @property int $status
 * @property int $is_trending
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CourseCategory> $subCategories
 * @property-read int|null $sub_categories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereIsTrending($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseCategory whereUpdatedAt($value)
 */
	class CourseCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLanguage whereUpdatedAt($value)
 */
	class CourseLanguage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseLevel whereUpdatedAt($value)
 */
	class CourseLevel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Admin
 *
 * @method \Laravel\Sanctum\NewAccessToken createToken(string $name, array $abilities = ['*'])
 * @property int $id
 * @property string $image
 * @property string $name
 * @property string|null $headline
 * @property string|null $bio
 * @property string|null $document
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string|null $gender
 * @property string|null $facebook
 * @property string|null $twitter
 * @property string|null $linkedin
 * @property string|null $github
 * @property string|null $website
 * @property string|null $login_as
 * @property string $approved_status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereApprovedStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFacebook($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGithub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinkedin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLoginAs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWebsite($value)
 */
	class User extends \Eloquent {}
}

