<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_approved',
        'approved_at',
    ];
    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // public function createdQuizzes()
    // {
    //     return $this->hasMany(Quiz::class, 'created_by');
    // }

    // public function assignedQuizzes()
    // {
    //     return $this->belongsToMany(Quiz::class, 'quiz_student', 'student_id', 'quiz_id');
    // }
    // public function students()
    // {
    //     return $this->belongsToMany(User::class, 'teacher_student', 'teacher_id', 'student_id');
    // }
    // public function teachers()
    // {
    //     return $this->belongsToMany(User::class, 'teacher_student', 'student_id', 'teacher_id');
    // }

    // public function submissions()
    // {
    //     return $this->hasMany(Submission::class);
    // }
    // public function hasAnyRole($roles)
    // {
    //     return $this->roles()->whereIn('name', $roles)->exists();
    // }

    // // add the recent function to the User model

    // public function taughtSubjects()
    // {
    //     return $this->belongsToMany(Subject::class, 'teacher_subject_class', 'teacher_id', 'subject_id')
    //         ->withPivot('class_id');
    // }
    // public function taughtClasses()
    // {
    //     return $this->belongsToMany(ClassGroup::class, 'teacher_subject_class', 'teacher_id', 'class_id')
    //         ->withPivot('subject_id');
    // }

    // public function enrolledClasses()
    // {
    //     return $this->belongsToMany(ClassGroup::class, 'student_class', 'student_id', 'class_id')
    //         ->withTimestamps();
    // }
    // public function enrollSubjectsStudent()
    // {
    //     return $this->belongsToMany(Subject::class, 'student_subject', 'student_id', 'subject_id')
    //         ->withPivot('class_id')
    //         ->withTimestamps();
    // }

    // public function approvedBy()
    // {
    //     return $this->belongsTo(User::class, 'approved_by');
    // }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
