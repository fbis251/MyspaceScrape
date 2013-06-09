MyspaceScrape

The license for my project applies to every file that does not claim to use another license.

All files that do not say otherwise were authored by me, Fernando Barillas

I believe I stopped updating this in 2008/2009 when I got tired of playing the cat and mouse game with Myspace.

Many hours went into this project, and it was my first major attempt at object oriented PHP in a production site.

I had a lot of fun writing this. It first started around 2006/2007 as a Myspace photo viewer. There was a specific URL for Myspace profiles that allowed you to get some XML containing all the user's pictures / albums, so I made a parser for it that displayed every single picture in one page unlike Myspace which paginated the content. Eventually I added some javascript to handle hover to zoom the images and even loading the full-sized images instead of thumbnails. Eventually I got fed up with looking at profiles people had which were littered with CSS that made their pages flash banners at you while having a bright green background with yellow text. The scraper would get all the code for a user's profile and would display it in a standard view, similar to Facebook profiles, but not nearly as pretty.

The most powerful parts of the project were the access class (msLogin.php, which allowed you to log into Myspace with your own Myspace profile username/password) and the profile parser (msProfile*.php which used a lot of regular expressions to pull data from a profile HTML page).

I also wish I had written a better template system to be able to display the profiles in a cleaner way.

I've had a lot of fun reading through my code again, seeing how I used to program. I hope that someone else can benefit from this code.

README last updated Sat Jun 8, 2013
