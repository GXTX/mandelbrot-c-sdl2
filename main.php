<?php
// This file has no copyright.
// Contact : http://geographer.fr
//           geographer@geographer.fr
require_once "../php-sdl/examples/bootstrap.php";

define("WINDOW_WIDTH", "640");
define("WINDOW_HEIGHT", "480");
define("WINDOW_TITLE", "Mandelbrot Fractal, by Geographer");

class SDL {
    public SDL_Window $window;
    public /* SDL_Renderer */ $renderer;
    public SDL_Surface $surface;
    public SDL_Joystick $joystick;
    public SDL_Event $event;

    public function __construct()
    {
        SDL_Init(SDL_INIT_VIDEO | SDL_INIT_JOYSTICK);
        $this->window   = SDL_CreateWindow(WINDOW_TITLE, SDL_WINDOWPOS_UNDEFINED, SDL_WINDOWPOS_UNDEFINED,
            WINDOW_WIDTH, WINDOW_HEIGHT, SDL_WINDOW_SHOWN);
        $this->renderer = SDL_CreateRenderer($this->window, -1, SDL_RENDERER_ACCELERATED);
        $this->surface  = SDL_GetWindowSurface($this->window);
        $this->joystick = SDL_JoystickOpen(0);
        $this->event    = new SDL_Event;
    }
}

class Complex
{
    public float $r = 0;
    public float $i = 0;
    public float $b = 0;
}

class Fractal
{
    public float $xMove = 0;
    public float $yMove = 0;

    public static int $iMax   = 60;
    public static float $zoom = 0.3;

    public function drawFractal(SDL $sdl) : void
    {
        $c = new Complex();
        $z = new Complex();

        SDL_LockSurface($sdl->surface);

        //$pixels      = $sdl->surface->pixels; // SIGSEGV
        $pixelFormat = $sdl->surface->format;

        for($y = 0; $y < WINDOW_HEIGHT; $y++) {
            $c->i = (($y - WINDOW_HEIGHT / 2) / (0.5 * WINDOW_HEIGHT * self::$zoom)) - $this->yMove;

            for($x = 0; $x < WINDOW_WIDTH; $x++) {
                $c->r = (($x - WINDOW_WIDTH / 2) / (0.5 * WINDOW_WIDTH * self::$zoom)) - $this->xMove;

                $i = 0;

                do {
                    $z->b = $z->r;
                    $z->r = $z->r * $z->r - $z->i * $z->i + $c->r;
                    $z->i = 2 * $z->i * $z->b + $c->i;
                    $i++;
                } while ($z->r * $z->r + $z->i * $z->i < 4 && $i < self::$iMax);

                if ($i >= self::$iMax) {
                    //$pixels[($y * WINDOW_WIDTH + $x)] = SDL_MapRGB($pixelFormat, 0 ,0 , 255);
                    $sdl->surface->pixels[($y * WINDOW_WIDTH + $x)] = 
                        SDL_MapRGB($pixelFormat, 0 ,0 , 255);
                }
                else {
                    //$pixels[($y * WINDOW_WIDTH + $x)] = SDL_MapRGB($pixelFormat, 0, 0, ($i * (255 / $this->iMax)));
                    $sdl->surface->pixels[($y * WINDOW_WIDTH + $x)] = 
                        SDL_MapRGB($pixelFormat, 0, 0, ($i * (255 / self::$iMax)));
                }
            }
        }

        SDL_UnlockSurface($sdl->surface);
    }
}

$delta    = 0.3;
$moveStep = 0.5;
$zoomStep = 3.0;

$sdl     = new SDL();
$fractal = new Fractal();

// Draw the initial fractal
$fractal->drawFractal($sdl);

while (1) {
    while (SDL_PollEvent($sdl->event)) { // Eventually look for joystick movement and update
        switch ($sdl->event->type) {
            case SDL_QUIT:
                print "Killing everything...\n";
                goto end; // BAD BAD BAD
                break;
            default:
                break;
        }
    }
    print "Update...\n";
    $fractal->drawFractal($sdl);
    SDL_RenderPresent($sdl->renderer);
}

end: // BAD BAD BAD
SDL_JoystickClose($sdl->joystick);
SDL_DestroyRenderer($sdl->renderer);
SDL_DestroyWindow($sdl->window);
SDL_Quit();

