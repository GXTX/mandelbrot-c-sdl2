XBE_TITLE = sdl-mandel
GEN_XISO = $(XBE_TITLE).iso
SRCS += $(wildcard $(CURDIR)/*.c)
NXDK_DIR = $(CURDIR)/../..
NXDK_SDL = y
include $(NXDK_DIR)/Makefile
