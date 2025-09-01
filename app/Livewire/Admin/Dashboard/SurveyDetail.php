<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\JawabanSurvey;
use Livewire\Component;

class SurveyDetail extends Component
{
    public JawabanSurvey $survey;

    // Method mount akan menerima data survei secara otomatis dari route
    public function mount(JawabanSurvey $jawabanSurvey)
    {
        // Kita muat relasi yang dibutuhkan agar tidak ada query berulang di view
        $jawabanSurvey->load('satker', 'jawabanItems.kuesioner');
        $this->survey = $jawabanSurvey;
    }

    public function render()
    {
        return view('livewire.admin.dashboard.survey-detail'); // Menggunakan layout admin
    }
}
