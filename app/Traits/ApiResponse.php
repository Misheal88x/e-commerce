<?php

declare(strict_types=1);

namespace App\Traits;

use Core\Response;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Success.', int $status = 200): Response
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully.'): Response
    {
        return $this->success($data, $message, 201);
    }

    protected function updated(mixed $data = null, string $message = 'Updated successfully.'): Response
    {
        return $this->success($data, $message);
    }

    protected function deleted(string $message = 'Deleted successfully.'): Response
    {
        return $this->success(null, $message);
    }

    protected function noContent(): Response
    {
        return Response::empty(204);
    }

    protected function paginated(array $result, string $message = 'Success.'): Response
    {
        return Response::json([
            'success'    => true,
            'message'    => $message,
            'data'       => $result['data'],
            'pagination' => $result['pagination'],
        ], 200);
    }

    protected function error(string $message, int $status = 400, array $errors = []): Response
    {
        $body = ['success' => false, 'message' => $message];
        if (!empty($errors)) {
            $body['errors'] = $errors;
        }
        return Response::json($body, $status);
    }
}
