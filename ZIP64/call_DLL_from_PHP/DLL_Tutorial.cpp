#include <iostream>

#define DLL_EXPORT
#include "DLL_Tutorial.h"

extern "C"
{
   DECLDIR int Add( int a, int b )
   {
      return( a + b );
   }

   DECLDIR void testFunction( void )
   {
      std::cout << "DLL Called!" << std::endl;
   }
}
