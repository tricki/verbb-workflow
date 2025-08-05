<?php
namespace verbb\workflow\controllers;

use verbb\workflow\Workflow;
use verbb\workflow\elements\Submission;

use Craft;
use craft\db\Table;
use craft\elements\User;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use craft\web\Controller;

use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReviewsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionCompare(?int $newReviewId = null, ?int $oldReviewId = null): Response
    {
        $this->requireCpRequest();

        $reviewsService = Workflow::$plugin->getReviews();

        $newReview = $reviewsService->getReviewById($newReviewId);
        $oldReview = $reviewsService->getReviewById($oldReviewId);

        if (!$newReview || !$oldReview) {
            throw new NotFoundHttpException('Review not found');
        }

        $variables = [
            'newReview' => $newReview,
            'oldReview' => $oldReview,
            'diff' => Workflow::$plugin->getContent()->getDiff(($oldReview->data ?? []), ($newReview->data ?? [])),
            'title' => "Compare review #{$oldReview->id} to #{$newReview->id}",
        ];

        return $this->renderTemplate('workflow/reviews/_compare', $variables);
    }

    public function actionCompareWithLive(?int $reviewId = null): Response
    {
        $this->requireCpRequest();

        $reviewsService = Workflow::$plugin->getReviews();
        $contentService = Workflow::$plugin->getContent();

        $review = $reviewsService->getReviewById($reviewId);

        if (!$review) {
            throw new NotFoundHttpException('Review not found');
        }

        $submission = $review->getSubmission();
        if (!$submission) {
            throw new NotFoundHttpException('Submission not found');
        }

        $liveEntry = $submission->getOwner();
        if (!$liveEntry) {
            throw new NotFoundHttpException('Live entry not found');
        }

        $liveEntryData = $contentService->getRevisionData($liveEntry);
        $reviewData = $review->data ?? [];

        $variables = [
            'review' => $review,
            'liveEntry' => $liveEntry,
            'diff' => $contentService->getDiff($liveEntryData, $reviewData),
            'title' => "Compare draft to live entry",
        ];

        return $this->renderTemplate('workflow/reviews/_compare-with-live', $variables);
    }

    public function actionCompareSelector(?int $submissionId = null): Response
    {
        $this->requireCpRequest();

        $submission = Workflow::$plugin->getSubmissions()->getSubmissionById($submissionId);

        if (!$submission) {
            throw new NotFoundHttpException('Submission not found');
        }

        $reviews = $submission->getReviews();
        $liveEntry = $submission->getOwner();

        $variables = [
            'submission' => $submission,
            'reviews' => $reviews,
            'liveEntry' => $liveEntry,
            'title' => 'Select Comparison Target',
        ];

        return $this->renderTemplate('workflow/reviews/_compare-selector', $variables);
    }

    public function actionCompareCustom(): Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $sourceType = $this->request->getParam('sourceType');
        $sourceId = $this->request->getParam('sourceId');
        $targetType = $this->request->getParam('targetType');
        $targetId = $this->request->getParam('targetId');

        if ($sourceType === 'review' && $targetType === 'review') {
            return $this->redirect(UrlHelper::cpUrl("workflow/reviews/compare/{$sourceId}:{$targetId}"));
        } elseif ($targetType === 'live') {
            return $this->redirect(UrlHelper::cpUrl("workflow/reviews/compare-with-live/{$sourceId}"));
        }

        throw new NotFoundHttpException('Invalid comparison type');
    }

    public function actionDeleteReview(): Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $session = Craft::$app->getSession();

        $reviewId = $this->request->getParam('reviewId');

        if (!Workflow::$plugin->getReviews()->deleteReviewById($reviewId)) {
            $session->setError(Craft::t('workflow', 'Unable to delete review.'));

            return null;
        }

        $session->setNotice(Craft::t('workflow', 'Review deleted.'));

        return $this->redirectToPostedUrl();
    }

    public function actionGetCompareModalBody(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $view = $this->getView();
        $reviewsService = Workflow::$plugin->getReviews();

        $reviewId = $this->request->getParam('reviewId');
        $newReview = $reviewsService->getReviewById($reviewId);

        // Get the previous review
        $oldReview = $reviewsService->getPreviousReviewById($reviewId);

        $view->registerAssetBundle(\verbb\workflow\assetbundles\WorkflowAsset::class);

        $html = $view->renderTemplate('workflow/reviews/_compare-modal', [
            'review' => $newReview,
            'diff' => Workflow::$plugin->getContent()->getDiff(($oldReview->data ?? []), ($newReview->data ?? [])),
        ]);

        $headHtml = $view->getHeadHtml();
        $footHtml = $view->getBodyHtml();

        return $this->asJson([
            'success' => true,
            'html' => $html,
            'headHtml' => $headHtml,
            'footHtml' => $footHtml,
        ]);
    }
}
