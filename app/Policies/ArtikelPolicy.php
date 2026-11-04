<?php

namespace App\Policies;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArtikelPolicy
{
    public function viewAny(User $user): Response
    {
        return $this->create($user);
    }

    public function create(User $user): Response
    {
        return $user->status === 'aktif' ? Response::allow() : $this->denied();
    }

    public function view(User $user, Artikel $artikel): Response
    {
        return $user->status === 'aktif' && ($user->id === $artikel->user_id || $user->isAdmin())
            ? Response::allow() : $this->denied();
    }

    public function update(User $user, Artikel $artikel): Response
    {
        return $user->status === 'aktif' && $user->id === $artikel->user_id
            ? Response::allow() : $this->denied();
    }

    public function delete(User $user, Artikel $artikel): Response
    {
        return $this->update($user, $artikel);
    }

    public function moderate(User $user, Artikel $artikel): Response
    {
        return $user->status === 'aktif' && $user->isAdmin() ? Response::allow() : $this->denied();
    }

    private function denied(): Response
    {
        return Response::deny(__('article.messages.forbidden'));
    }
}
