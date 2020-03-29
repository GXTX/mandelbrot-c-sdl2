<?php
// This file has no copyright.
// Contact : http://geographer.fr
//           geographer@geographer.fr
require_once "../php-sdl/examples/bootstrap.php";

define("WINDOW_WIDTH", "640");
define("WINDOW_HEIGHT", "480");
define("WINDOW_TITLE", "Mandelbrot Fractal, by Geographer");

class Complex
{
    public float $r = 0;
    public float $i = 0;
    public float $b = 0;
}

class SDL {
    public SDL_Window $window;
    public /* SDL_Renderer */ $renderer;
    public SDL_Event $event;

    public function __construct()
    {
        SDL_Init(SDL_INIT_EVERYTHING);
        $this->window   = SDL_CreateWindow(WINDOW_TITLE, SDL_WINDOWPOS_UNDEFINED, SDL_WINDOWPOS_UNDEFINED,
            WINDOW_WIDTH, WINDOW_HEIGHT, SDL_WINDOW_SHOWN);
        $this->renderer = SDL_CreateRenderer($this->window, -1, SDL_RENDERER_ACCELERATED);
        $this->event    = new SDL_Event;
    }
}

class Fractal
{
    public float $xMove = 0;
    public float $yMove = 0;

    public int $iMax   = 15;
    public float $zoom = 0.3;

    public function drawFractal(SDL $sdl) : void
    {
        $c = new Complex();
        $z = new Complex();

        for($y = 0; $y < WINDOW_HEIGHT; $y++) {
            $c->i = (($y - WINDOW_HEIGHT / 2) / (0.5 * WINDOW_HEIGHT * $this->zoom)) - $this->yMove;

            for($x = 0; $x < WINDOW_WIDTH; $x++) {
                $c->r = (($x - WINDOW_WIDTH / 2) / (0.5 * WINDOW_WIDTH * $this->zoom)) - $this->xMove;

                $z->r = 0;
                $z->i = 0;

                $i = 0;

                while(($z->r * $z->r) + ($z->i * $z->i) < 4 && $i < $this->iMax) {
                    $z->b = $z->r;
                    $z->r = $z->r * $z->r - $z->i * $z->i + $c->r;
                    $z->i = 2 * $z->i * $z->b + $c->i;
                    $i++;
                }

                if ($i >= $this->iMax) {
                    SDL_SetRenderDrawColor($sdl->renderer, 0, 0, 255, 255);
                }
                else {
                    SDL_SetRenderDrawColor($sdl->renderer, 0, 0, round(($i * (255 / $this->iMax))), 255);
                }

                SDL_RenderDrawPoint($sdl->renderer, $x, $y);
            }
        }
        SDL_RenderPresent($sdl->renderer);
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
    while(SDL_PollEvent($sdl->event)) {
        switch ($sdl->event->type) {
            case SDL_KEYDOWN:
                switch($sdl->event->key->keysym->sym) {
                    case SDLK_LEFT:
                        $fractal->xMove = $fractal->xMove + ($moveStep / $fractal->zoom * $delta);
                        break;
                    case SDLK_RIGHT:
                        $fractal->xMove = $fractal->xMove - ($moveStep / $fractal->zoom * $delta);
                        break;
                    case SDLK_UP:
                        $fractal->yMove = $fractal->yMove + ($moveStep / $fractal->zoom * $delta);
                        break;
                    case SDLK_DOWN:
                        $fractal->yMove = $fractal->yMove - ($moveStep / $fractal->zoom * $delta);
                        break;
                    case SDLK_RETURN:
                        $fractal->zoom = $fractal->zoom + ($moveStep * $fractal->zoom * $delta);
                        $fractal->iMax = $fractal->iMax + $zoomStep * $delta;
                        break;
                    case SDLK_BACKSPACE:
                        $fractal->zoom = $fractal->zoom - ($moveStep * $fractal->zoom * $delta);
                        $fractal->iMax = $fractal->iMax - $zoomStep * $delta;
                        break;
                    default:
                        break;
                }
                break;
            case SDL_QUIT:
                print "Killing everything...\n";
                return;
                break;
            default:
                break;
        }
    }
    print "Update...\n";
    $fractal->drawFractal($sdl);
}

SDL_DestroyRenderer($sdl->renderer);
SDL_DestroyWindow($sdl->window);
SDL_Quit();

