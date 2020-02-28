// This file has no copyright.
// Contact : http://geographer.fr
//           geographer@geographer.fr
#include <hal/debug.h>
#include <hal/video.h>
#include <assert.h>
#include <SDL.h>

#define WINDOW_WIDTH  640
#define WINDOW_HEIGHT 480
#define WINDOW_TITLE "Mandelbrot Fractal, by Geographer"

typedef struct Sdl {
  SDL_Window *window;
  SDL_Renderer *renderer;
  SDL_Surface *surface;
  SDL_Event event;
  SDL_GameController *controller;
} Sdl;

typedef struct Complex {
  // Real part, imaginary part and a backup
  float r;
  float i;
  float b;
} Complex;

typedef struct Fractal {
  float xMove;
  float yMove;
  float zoom;
  unsigned int iMax;
} Fractal;

////////////////////////////////////////////////////////////////////////////////////////////////////

Sdl *init_sdl() {
  Sdl *sdl = malloc(sizeof(Sdl));

  SDL_Init(SDL_INIT_VIDEO | SDL_INIT_GAMECONTROLLER);
  SDL_SetHint(SDL_HINT_JOYSTICK_ALLOW_BACKGROUND_EVENTS, "1");

  SDL_CreateWindowAndRenderer(WINDOW_WIDTH, WINDOW_HEIGHT, 
    SDL_WINDOW_SHOWN, &sdl->window, &sdl->renderer);

  sdl->surface = SDL_GetWindowSurface(sdl->window);
  sdl->controller = SDL_GameControllerOpen(0);

  // Force a controller present
  assert(sdl->controller != NULL);

  return sdl;
}

Fractal *init_fractal() {
  Fractal *fractal = malloc(sizeof(Fractal));

  // Used to move camera
  fractal->xMove = 0;
  fractal->yMove = 0;

  // Used to change the zoom and precision
  fractal->zoom = 0.3;
  fractal->iMax = 60;

  return fractal;
}

void draw_mandelbrot(Sdl *sdl, Fractal *fractal) {
  int i;

  SDL_LockSurface(sdl->surface);

  uint32_t *pixels = sdl->surface->pixels;

  int xFrame = WINDOW_WIDTH;
  int yFrame = WINDOW_HEIGHT;

  // Formula is Z(n+1) = Z(n)^2 + C
  // https://en.wikipedia.org/wiki/Mandelbrot_set
  Complex c;
  Complex z;

  // Coordonate of each point
  int x;
  int y;

  // Calculate all the y for every x
  for (y = 0; y < yFrame; y++) {
    c.i = ((y - yFrame / 2) / (0.5 * yFrame * fractal->zoom)) - fractal->yMove;

    for (x = 0; x < xFrame; x++) {
      c.r = ((x - xFrame / 2) / (0.5 * xFrame * fractal->zoom)) - fractal->xMove;

      z.r = 0;
      z.i = 0;

      i = 0;

      // Iterate in order to know if a certain point is in the set or not
      do {
        z.b = z.r;
        z.r = z.r * z.r - z.i * z.i + c.r;
        z.i = 2 * z.i * z.b + c.i;
        i++;
      } while (z.r * z.r + z.i * z.i < 4 && i < fractal->iMax);
      // We don't use square root in order to reduce calculation time

      // BAD BAD
      uint32_t color;
      color += 0xFF;
      color <<= 8;
      color += 0x00;
      color <<= 8;
      color += 0x00;

      if (i >= fractal->iMax) {
        // In the set
        color <<= 8;
        color += 0xFF;
        pixels[(y * xFrame + x)] = color;
      } else {
        // Not in the set
        color <<= 8;
        color += i * (255 / fractal->iMax);
        pixels[(y * xFrame + x)] = color;
      }
    }
  }
  SDL_UnlockSurface(sdl->surface);
}

int main(void) {
  // Delta time to sync everything
  float delta = 0.30;

  // Movement speed
  float moveStep = 0.5;
  float zoomStep = 3.0;

  XVideoSetMode(WINDOW_WIDTH, WINDOW_HEIGHT, 32, REFRESH_DEFAULT);

  // Init the structures
  Sdl *sdl = init_sdl();
  Fractal *fractal = init_fractal();

  // Draw the inital
  draw_mandelbrot(sdl, fractal);

  while (1) {
    while (SDL_PollEvent(&sdl->event)) {
      if (sdl->event.type == SDL_CONTROLLERBUTTONDOWN) {
        switch (sdl->event.cbutton.button) {
          case SDL_CONTROLLER_BUTTON_DPAD_LEFT:
            fractal->xMove = fractal->xMove + (moveStep / fractal->zoom * delta);
            break;
          case SDL_CONTROLLER_BUTTON_DPAD_RIGHT:
            fractal->xMove = fractal->xMove - (moveStep / fractal->zoom * delta);
            break;
          case SDL_CONTROLLER_BUTTON_DPAD_UP:
            fractal->yMove = fractal->yMove + (moveStep / fractal->zoom * delta);
            break;
          case SDL_CONTROLLER_BUTTON_DPAD_DOWN:
            fractal->yMove = fractal->yMove - (moveStep / fractal->zoom * delta);
            break;
          case SDL_CONTROLLER_BUTTON_A:
            fractal->zoom = fractal->zoom + (moveStep * fractal->zoom * delta);
            fractal->iMax = fractal->iMax + zoomStep * delta;
            break;
          case SDL_CONTROLLER_BUTTON_Y:
            fractal->zoom = fractal->zoom - (moveStep * fractal->zoom * delta);
            fractal->iMax = fractal->iMax - zoomStep * delta;
            break;
          default:
            break;
        }
        debugPrint("Update\n");
        draw_mandelbrot(sdl, fractal);
      }
    }
    SDL_RenderPresent(sdl->renderer);
  }

  return 0;
}

