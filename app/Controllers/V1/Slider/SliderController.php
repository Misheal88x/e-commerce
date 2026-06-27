<?php

declare(strict_types=1);

namespace App\Controllers\V1\Slider;

use App\Controllers\BaseController;
use App\DTOs\Slider\SliderDTO;
use App\Services\Slider\SliderService;
use App\Validators\Slider\CreateSliderValidator;
use App\Validators\Slider\UpdateSliderValidator;
use Core\Request;
use Core\Response;

class SliderController extends BaseController
{
    public function __construct(
        Request $request,
        private readonly SliderService $sliderService,
    ) {
        parent::__construct($request);
    }

    public function index(Request $request): Response
    {
        $sliders = $this->sliderService->getAll();
        return $this->success($sliders, 'Sliders retrieved.');
    }

    public function show(Request $request): Response
    {
        $id     = (int) $request->param('id');
        $slider = $this->sliderService->getById($id);
        return $this->success($slider, 'Slider retrieved.');
    }

    public function store(Request $request): Response
    {
        $data      = (new CreateSliderValidator())->validate($request->body());
        $imageFile = $request->file('image');
        $dto       = SliderDTO::fromArray($data);
        $slider    = $this->sliderService->create($dto, $imageFile);
        return $this->created($slider, 'Slider created successfully.');
    }

    public function update(Request $request): Response
    {
        $id        = (int) $request->param('id');
        $data      = (new UpdateSliderValidator())->validate($request->body());
        $imageFile = $request->file('image');
        $slider    = $this->sliderService->update($id, $data, $imageFile);
        return $this->updated($slider, 'Slider updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->sliderService->delete($id);
        return $this->deleted('Slider deleted successfully.');
    }
}
