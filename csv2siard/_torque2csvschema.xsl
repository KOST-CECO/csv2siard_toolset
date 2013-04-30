<?xml version="1.0" encoding="UTF-8"?>
<!-- edited with XMLSpy v2012 rel. 2 (http://www.altova.com) by Thomas Bula (Bundesamt für Informatik und Telekommunikation) -->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:db="http://db.apache.org/torque/4.0/templates/database">
	<xsl:output method="xml" indent="no" encoding="ISO-8859-1" omit-xml-declaration="yes"/>
	<xsl:variable name="location">http://www.bar.admin.ch/xmlns/siard/1.0/metadata.xsd metadata.xsd</xsl:variable>
	<!--                        begin params                       -->
	<xsl:param name="file_mask"/>
	<xsl:param name="column_names"/>
	<xsl:param name="delimited"/>
	<xsl:param name="charset"/>
	<!--                        end params                          -->
	<xsl:variable name="newline">
		<xsl:text>&#xD;&#xA;</xsl:text>
	</xsl:variable>
	<!--                        end variables                       -->
	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>
	<!--                          TABLES                            -->
	<xsl:template match="/db:database/db:table">
		<xsl:value-of select="$newline"/>
		<xsl:text>[</xsl:text>
		<xsl:value-of select="@name"/>
		<xsl:value-of select="$file_mask"/>
		<xsl:text>]</xsl:text>
		<xsl:value-of select="$newline"/>
		<xsl:text>ColNameHeader=</xsl:text>
		<xsl:value-of select="$column_names"/>
		<xsl:value-of select="$newline"/>
		<xsl:text>Format=Delimited(</xsl:text>
		<xsl:value-of select="$delimited"/>
		<xsl:text>)</xsl:text>
		<xsl:value-of select="$newline"/>
		<xsl:text>MaxScanRows=25</xsl:text>
		<xsl:value-of select="$newline"/>
		<xsl:text>CharacterSet=</xsl:text>
		<xsl:value-of select="$charset"/>
		<xsl:value-of select="$newline"/>
		<!--                           ROWS                            -->
		<xsl:for-each select="db:column">
			<xsl:text>Col</xsl:text>
			<xsl:value-of select="position()"/>
			<xsl:text>=</xsl:text>
			<xsl:value-of select="@name"/>
			<xsl:text/>
			<xsl:choose>
				<xsl:when test="@type='INTEGER'">
					<xsl:text>Integer</xsl:text>
				</xsl:when>
				<xsl:when test="@type='DECIMAL'">
					<xsl:text>Float</xsl:text>
				</xsl:when>
				<xsl:when test="@type='FLOAT'">
					<xsl:text>Float</xsl:text>
				</xsl:when>
				<xsl:when test="@type='DATE'">
					<xsl:text>Datetime</xsl:text>
				</xsl:when>
				<xsl:when test="@type='VARCHAR'">
					<xsl:text>Char </xsl:text>
					<xsl:if test="@size">
						<xsl:text>width </xsl:text>
						<xsl:value-of select="@size"/>
					</xsl:if>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>Char</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:value-of select="$newline"/>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
