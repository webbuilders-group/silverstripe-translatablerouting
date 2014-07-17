<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type='text/xsl' href='{$BaseHref}googlesitemaps/templates/xml-sitemap.xsl'?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
	<% loop $Items %>
        <url>
            <loc>$AbsoluteLink</loc>
           	<% if $LastEdited %><lastmod>$LastEdited.Format(c)</lastmod><% end_if %>
            <% if $ChangeFreq %><changefreq>$ChangeFreq</changefreq><% end_if %>
            <% if $Priority %><priority>$Priority</priority><% end_if %>
            <% if $Translations.Count %>
                <xhtml:link rel="alternate" hreflang="$RFC1766Locale" href="$AbsoluteLink"/>
                <% loop $Translations %>
                    <xhtml:link rel="alternate" hreflang="$RFC1766Locale" href="$AbsoluteLink"/>
                <% end_loop %>
            <% end_if %>
        </url>
	<% end_loop %>
</urlset>