<?php

declare(strict_types=1);

namespace App\Controllers\V1\DeliveryMethod;

use App\Controllers\BaseController;
use App\DTOs\DeliveryMethod\DeliveryMethodDTO;
use App\Services\DeliveryMethod\DeliveryMethodService;
use App\Validators\DeliveryMethod\CreateDeliveryMethodValidator;
use App\Validators\DeliveryMethod\UpdateDeliveryMethodValidator;
use Core\Request;
use Core\Response;

class DeliveryMethodController extends BaseController
{
    public function __construct(
        Request $request,
        private readonly DeliveryMethodService $deliveryMethodService,
    ) {
        parent::__construct($request);
    }

    public function index(Request $request): Response
    {
        $deliveryMethods = $this->deliveryMethodService->getAllActive();
        return $this->success($deliveryMethods, 'Delivery methods retrieved.');
    }

    public function show(Request $request): Response
    {
        $id             = (int) $request->param('id');
        $deliveryMethod = $this->deliveryMethodService->getById($id);
        return $this->success($deliveryMethod, 'Delivery method retrieved.');
    }

    public function store(Request $request): Response
    {
        $data           = (new CreateDeliveryMethodValidator())->validate($request->body());
        $dto            = DeliveryMethodDTO::fromArray($data);
        $deliveryMethod = $this->deliveryMethodService->create($dto);
        return $this->created($deliveryMethod, 'Delivery method created successfully.');
    }

    public function update(Request $request): Response
    {
        $id             = (int) $request->param('id');
        $data           = (new UpdateDeliveryMethodValidator())->validate($request->body());
        $deliveryMethod = $this->deliveryMethodService->update($id, $data);
        return $this->updated($deliveryMethod, 'Delivery method updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->deliveryMethodService->delete($id);
        return $this->deleted('Delivery method deleted successfully.');
    }
}
