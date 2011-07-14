<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:db="http://db.apache.org/torque/4.0/templates/database" xmlns="http://www.bar.admin.ch/xmlns/siard/1.0/metadata.xsd">
	<xsl:output method="xml" indent="yes" encoding="UTF-8" media-type="application/xml"/>
	<xsl:variable name="location">http://www.bar.admin.ch/xmlns/siard/1.0/metadata.xsd metadata.xsd</xsl:variable>
	<!--                        begin params                       -->
	<!-- <xsl:param name="archivalDate"/>  -->
	<!-- <xsl:param name="databaseUser"/>  -->
	<xsl:param name="archivalDate">2010-12-28</xsl:param>
	<xsl:param name="databaseUser">admin</xsl:param>
	<xsl:param name="databaseSchema">admin</xsl:param>
	<xsl:param name="messageDigest">MD5</xsl:param>
	<!--                        end params                         -->
	<xsl:template match="/">
		<xsl:element name="siardArchive">
			<xsl:attribute name="version"><xsl:text>1.0</xsl:text></xsl:attribute>
			<xsl:attribute name="xsi:schemaLocation"><xsl:text>http://www.bar.admin.ch/xmlns/siard/1.0/metadata.xsd metadata.xsd</xsl:text></xsl:attribute>
			<xsl:element name="dbname">
				<xsl:value-of select="/db:database/@name"/>
			</xsl:element>
			<xsl:element name="dataOwner">
				<xsl:text>(...)</xsl:text>
			</xsl:element>
			<xsl:element name="dataOriginTimespan">
				<xsl:text>(...)</xsl:text>
			</xsl:element>
			<xsl:element name="producerApplication">
				<xsl:text>csv2siard</xsl:text>
			</xsl:element>
			<xsl:element name="archivalDate">
				<xsl:value-of select="$archivalDate"/>
			</xsl:element>
			<xsl:element name="messageDigest">
				<xsl:value-of select="$messageDigest"/>
			</xsl:element>
			<xsl:element name="databaseProduct">
				<xsl:text>CSV</xsl:text>
			</xsl:element>
			<xsl:element name="databaseUser">
				<xsl:text>"</xsl:text>
				<xsl:value-of select="$databaseUser"/>
				<xsl:text>"</xsl:text>
			</xsl:element>
			<!-- SCHEMA -->
			<xsl:element name="schemas">
				<xsl:element name="schema">
					<xsl:element name="name">
						<xsl:value-of select="$databaseUser"/>
					</xsl:element>
					<xsl:element name="folder">
						<xsl:text>schema0</xsl:text>
					</xsl:element>
					<!-- TABLES -->
					<xsl:element name="tables">
						<xsl:apply-templates/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<!-- USER -->
			<xsl:element name="users">
				<xsl:element name="user">
					<xsl:element name="name">
						<xsl:value-of select="$databaseUser"/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	<!-- TABLE -->
	<xsl:template match="/db:database/db:table">
		<xsl:element name="table">
			<xsl:element name="name">
				<xsl:value-of select="@name"/>
			</xsl:element>
			<xsl:element name="folder">
				<xsl:value-of select="db:option[@key='folder']/@value"/>
			</xsl:element>
			<xsl:if test="@description">
				<xsl:element name="description">
					<xsl:value-of select="@description"/>
				</xsl:element>
			</xsl:if>
			<!-- COLUMNS -->
			<xsl:element name="columns">
				<xsl:apply-templates/>
			</xsl:element>
			<!-- PRIMARY KEY -->
			<xsl:if test="db:column/@primaryKey">
				<xsl:element name="primaryKey">
					<xsl:for-each select="db:column">
						<xsl:if test="@primaryKey">
							<xsl:element name="column">
								<xsl:value-of select="@name"/>
							</xsl:element>
						</xsl:if>
					</xsl:for-each>
				</xsl:element>
			</xsl:if>
			<!-- FOREIGN KEYS -->
			<xsl:if test="db:foreign-key">
				<xsl:element name="foreignKeys">
					<xsl:for-each select="db:foreign-key">
						<xsl:element name="foreignKey">
							<xsl:element name="name">
								<xsl:value-of select="@name"/>
							</xsl:element>
							<xsl:element name="referencedSchema">
								<xsl:value-of select="$databaseSchema"/>
							</xsl:element>
							<xsl:element name="referencedTable">
								<xsl:value-of select="@foreignTable"/>
							</xsl:element>
							<xsl:element name="reference">
								<xsl:element name="column">
									<xsl:value-of select="db:reference/@local"/>
								</xsl:element>
								<xsl:element name="referenced">
									<xsl:value-of select="db:reference/@foreign"/>
								</xsl:element>
							</xsl:element>
						</xsl:element>
					</xsl:for-each>
				</xsl:element>
			</xsl:if>
			<!-- ROWS -->
			<xsl:element name="rows">
				<xsl:value-of select="db:option[@key='rowcount']/@value"/>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	<!-- COLUMN -->
	<xsl:template match="/db:database/db:table/db:column">
		<xsl:element name="column">
			<xsl:element name="name">
				<xsl:value-of select="@name"/>
			</xsl:element>
			<xsl:element name="type">
				<xsl:value-of select="@type"/>
			</xsl:element>
			<xsl:element name="nullable">
				<xsl:text>true</xsl:text>
			</xsl:element>
			<xsl:if test="@description">
				<xsl:element name="description">
					<xsl:value-of select="@description"/>
				</xsl:element>
			</xsl:if>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
