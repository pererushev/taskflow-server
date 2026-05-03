<?php

namespace App\Http\Requests\Api\V1;

use App\DTOs\TaskData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return TaskData::rules(isUpdate: true);
    }
}
