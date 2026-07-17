<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CatNotFoundException;
use App\Models\Cat;
use App\Models\User;
use App\Validation\CatValidator;
use Illuminate\Database\Eloquent\Collection;

class CatService
{
    public function __construct(private CatValidator $validator) {}

    public function listForUser(User $user): Collection
    {
        return $user->cats()
            ->orderByDesc('created_at')
            ->get();
    }

    public function findForUser(User $user, int $catId): Cat
    {
        $cat = $user->cats()
            ->whereKey($catId)
            ->first();
        if (!$cat) {
            throw new CatNotFoundException();
        }

        return $cat;
    }

    public function createForUser(User $user, array $data): Cat {
        $validated = $this->validator
            ->validateForCreate($data);

        return $user->cats()->create($validated);
    }

    public function updateForUser(User $user, int $catId, array $data): Cat {
        $cat = $this->findForUser($user, $catId);

        $validated = $this->validator
            ->validateForUpdate($data);

        $cat->fill($validated);
        $cat->save();

        return $cat;
    }

    public function deleteForUser(User $user, int $catId): Cat {
        $cat = $this->findForUser($user, $catId);
        $cat->delete();

        return $cat;
    }
}