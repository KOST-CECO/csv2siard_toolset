<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:db="http://db.apache.org/torque/4.0/templates/database">
	<xsl:output method="text" indent="no" omit-xml-declaration="yes" encoding="UTF-8"/>
	<xsl:template match="/">
		<xsl:text> array (</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>);</xsl:text>
	</xsl:template>
	<!-- DATABASE -->
	<xsl:template match="/db:database">
		<xsl:text>"</xsl:text>
		<xsl:value-of select="@name"/>
		<xsl:text>"</xsl:text>
		<xsl:text> => array (</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>), </xsl:text>
	</xsl:template>
	<!-- TABLE -->
	<xsl:template match="/db:database/db:table">
		<xsl:text>"</xsl:text>
		<xsl:value-of select="@name"/>
		<xsl:text>"</xsl:text>
		<xsl:text> => array (</xsl:text>
		<xsl:apply-templates/>
		<xsl:text>), </xsl:text>
	</xsl:template>
	<!-- COLUMN -->
	<xsl:template match="/db:database/db:table/db:column">
		<xsl:text>"</xsl:text>
		<xsl:value-of select="@name"/>
		<xsl:text>"</xsl:text>
		<xsl:text> => array (</xsl:text>
		<!-- PROPERTY: name -->
		<xsl:text>"name" => </xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="@name"/>
		<xsl:text>"</xsl:text>
		<!-- PROPERTY: type -->
		<xsl:if test="@type">
			<xsl:text>, "type" => </xsl:text>
			<xsl:text>"</xsl:text>
			<xsl:value-of select="@type"/>
			<xsl:text>"</xsl:text>
		</xsl:if>
		<!-- PROPERTY: size -->
		<xsl:if test="@size">
			<xsl:text>, "size" => </xsl:text>
			<xsl:text>"</xsl:text>
			<xsl:value-of select="@size"/>
			<xsl:text>"</xsl:text>
		</xsl:if>
		<!-- PROPERTY: description -->
		<xsl:if test="@description">
			<xsl:text>, "description" => </xsl:text>
			<xsl:text>"</xsl:text>
			<xsl:value-of select="@description"/>
			<xsl:text>"</xsl:text>
		</xsl:if>
		<xsl:text>), </xsl:text>
	</xsl:template>
</xsl:stylesheet>
