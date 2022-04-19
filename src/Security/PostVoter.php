<?php

namespace App\Security;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PostVoter extends Voter
{
    // these strings are just invented: you can use anything
    public const VIEW = 'view';

    public const EDIT = 'edit';

    public const DELETE = 'delete';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (! in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (! $user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Post $post */
        $post = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($post, $user);
            case self::EDIT:
                return $this->canEdit($post, $user);
            case self::DELETE:
                return $this->canDelete($post, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Post $post, User $user): bool
    {
        if ($post->getApprove()) {
            return true;
        }

        if ($this->canEdit($post, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Post $post, User $user): bool
    {
        if ($user === $post->getUser()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_MODERATOR') && ! $post->getApprove()) {
            return true;
        }

        return false;
    }

    private function canDelete(Post $post, User $user): bool
    {
        if ($this->canEdit($post, $user)) {
            return true;
        }

        return false;
    }
}