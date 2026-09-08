<?php

declare(strict_types=1);

namespace App\Controllers\V1\PaymentType;

use App\Controllers\BaseController;
use App\DTOs\PaymentType\PaymentTypeDTO;
use App\Services\PaymentType\PaymentTypeService;
use App\Validators\PaymentType\CreatePaymentTypeValidator;
use App\Validators\PaymentType\UpdatePaymentTypeValidator;
use Core\Request;
use Core\Response;

class PaymentTypeController extends BaseController
{
    public function __construct(
        Request $request,
        private readonly PaymentTypeService $paymentTypeService,
    ) {
        parent::__construct($request);
    }

    public function index(Request $request): Response
    {
        $paymentTypes = $this->paymentTypeService->getAllActive();
        return $this->success($paymentTypes, 'Payment types retrieved.');
    }

    public function show(Request $request): Response
    {
        $id          = (int) $request->param('id');
        $paymentType = $this->paymentTypeService->getById($id);
        return $this->success($paymentType, 'Payment type retrieved.');
    }

    public function store(Request $request): Response
    {
        $data        = (new CreatePaymentTypeValidator())->validate($request->body());
        $dto         = PaymentTypeDTO::fromArray($data);
        $paymentType = $this->paymentTypeService->create($dto);
        return $this->created($paymentType, 'Payment type created successfully.');
    }

    public function update(Request $request): Response
    {
        $id          = (int) $request->param('id');
        $data        = (new UpdatePaymentTypeValidator())->validate($request->body());
        $paymentType = $this->paymentTypeService->update($id, $data);
        return $this->updated($paymentType, 'Payment type updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->paymentTypeService->delete($id);
        return $this->deleted('Payment type deleted successfully.');
    }
}
