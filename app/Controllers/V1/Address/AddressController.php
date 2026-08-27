<?php

declare(strict_types=1);

namespace App\Controllers\V1\Address;

use App\Controllers\BaseController;
use App\Services\Address\AddressService;
use App\Validators\Address\CreateAddressValidator;
use App\Validators\Address\UpdateAddressValidator;
use App\DTOs\Address\AddressDTO;
use Core\Request;
use Core\Response;

class AddressController extends BaseController
{
    private AddressService $addressService;

    public function __construct(Request $request, AddressService $addressService)
    {
        parent::__construct($request);
        $this->addressService = $addressService;
    }

    public function index(Request $request): Response
    {
        $addresses = $this->addressService->getUserAddresses($this->userId());
        return $this->success($addresses, 'Addresses retrieved.');
    }

    public function show(Request $request): Response
    {
        $id      = (int) $request->param('id');
        $address = $this->addressService->getAddress($id, $this->userId());
        return $this->success($address, 'Address retrieved.');
    }

    public function store(Request $request): Response
    {
        $data    = (new CreateAddressValidator())->validate($request->body());
        $dto     = AddressDTO::fromArray($data, $this->userId());
        $address = $this->addressService->create($dto);
        return $this->created($address, 'Address created successfully.');
    }

    public function update(Request $request): Response
    {
        $id      = (int) $request->param('id');
        $data    = (new UpdateAddressValidator())->validate($request->body());
        $dto     = AddressDTO::fromArray($data, $this->userId());
        $address = $this->addressService->update($id, $this->userId(), $dto);
        return $this->updated($address, 'Address updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->addressService->delete($id, $this->userId());
        return $this->deleted('Address deleted successfully.');
    }

    public function setDefault(Request $request): Response
    {
        $id      = (int) $request->param('id');
        $address = $this->addressService->setDefault($id, $this->userId());
        return $this->success($address, 'Default address updated.');
    }
}
