<?php

namespace App\Security;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CommentVoter extends Voter
{
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

        if (!$subject instanceof Comment) {
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

        /** @var Comment $comment */
        $comment = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($comment, $user);
            case self::EDIT:
                return $this->canEdit($comment, $user);
            case self::DELETE:
                return $this->canDelete($comment, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Comment $comment, User $user): bool
    {
        if ($comment->getApprove()) {
            return true;
        }

        if ($this->canEdit($comment, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Comment $comment, User $user): bool
    {
        if ($user === $comment->getUser()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_MODERATOR') && ! $comment->getApprove()) {
            return true;
        }

        return false;
    }

    private function canDelete(Comment $comment, User $user): bool
    {
        if ($this->canEdit($comment, $user)) {
            return true;
        }

        return false;
    }
}