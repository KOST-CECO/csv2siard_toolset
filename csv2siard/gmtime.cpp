// Set the tm_t fields for the local time. 
struct tm *gmtime(timep) const time_t *timep; 
{ 
   static struct tm tmbuf; 
   register struct tm *tp = &tmbuf; 
   time_t time = *timep; 
   register long day, mins, secs, year, leap; 
   day = time/(24L*60*60); 
   secs = time % (24L*60*60); 
   tp->tm_sec = secs % 60; 
   mins = secs / 60; 
   tp->tm_hour = mins / 60; 
   tp->tm_min = mins % 60; 
   tp->tm_wday = (day + 4) % 7; 
   year = (((day * 4) + 2)/1461); 
   tp->tm_year = year + 70; 
   leap = !(tp->tm_year & 3); 
   day -= ((year * 1461) + 1) / 4; 
   tp->tm_yday = day; 
   day += (day > 58 + leap) ? ((leap) ? 1 : 2) : 0; 
   tp->tm_mon = ((day * 12) + 6)/367; 
   tp->tm_mday = day + 1 - ((tp->tm_mon * 367) + 5)/12; 
   tp->tm_isdst = 0; 
   return (tp); 
} 