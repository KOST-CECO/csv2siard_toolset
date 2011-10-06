#
# description: makefile for DLL Tutorial
#
# to build the executables:
# make clean
# make
#

CC = cl.exe
LC = link.exe
CFLAGS += /I "." /EHsc /nologo

make:
	@ $(CC) DLL_Tutorial.cpp /LD $(CFLAGS)
	@ $(CC) DLL_test_implicit.cpp DLL_Tutorial.lib /FeDLL_test_implicit.exe $(CFLAGS)
	@ call bamcompile.exe DLL.bcp

clean:
	@ echo "Clean up DLL_Tutorial" 
	@ rm -f *.obj *.lib

test:
	DLL_test_implicit.exe
	call DLL_test.exe
