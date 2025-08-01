<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WelcomePageTest extends DuskTestCase
{
    /**
     * Test that we can visit the Laravel welcome page
     */
    public function test_can_visit_welcome_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->pause(1000) // Pause for 1 second to ensure page loads
                    ->assertSee('Laravel')
                    ->assertTitle('Laravel');
        });
    }

    /**
     * Test responsive design by resizing browser
     */
    public function test_welcome_page_is_responsive()
    {
        $this->browse(function (Browser $browser) {
            // Test desktop view
            $browser->visit('/')
                    ->pause(1000)
                    ->resize(1200, 800)
                    ->assertSee('Laravel')
                    
            // Test mobile view  
                    ->resize(375, 667)
                    ->assertSee('Laravel');
        });
    }
}