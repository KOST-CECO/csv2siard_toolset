<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:db="http://db.apache.org/torque/4.0/templates/database" xmlns="http://www.bar.admin.ch/xmlns/siard/1.0/metadata.xsd">
	<xsl:output method="xml" indent="yes" encoding="UTF-8" media-type="application/xml"/>
	<xsl:variable name="location">http://www.bar.admin.ch/xmlns/siard/1.0/metadata.xsd metadata.xsd</xsl:variable>
	<!--                        begin params                       -->
	<xsl:param name="description"/>
	<xsl:param name="archiver"/>
	<xsl:param name="archiverContact"/>
	<xsl:param name="dataOwner"/>
	<xsl:param name="dataOriginTimespan"/>
	<xsl:param name="producerApplication"/>
	<xsl:param name="archivalDate"/>
	<xsl:param name="messageDigest"/>
	<xsl:param name="clientMachine"/>
	<xsl:param name="databaseProduct"/>
	<xsl:param name="connection"/>
	<xsl:param name="databaseUser"/>
	<xsl:param name="databaseSchema"/>
	<!--                        end params                         -->
	<xsl:template match="/">
		<xsl:element name="siardArchive">
			<xsl:attribute name="version"><xsl:text>1.0</xsl:text></xsl:attribute>
			<xsl:attribute name="xsi:schemaLocation"><xsl:text>http://www.bar.admin.ch/xmlns/siard/1.0/metadata.xsd metadata.xsd</xsl:text></xsl:attribute>
			<xsl:element name="dbname">
				<xsl:choose>
					<xsl:when test="/db:database/@name=''">
						<xsl:text>unknown</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="/db:database/@name"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:element>
			<xsl:element name="description">
				<xsl:value-of select="$description"/>
			</xsl:element>
			<xsl:element name="archiver">
				<xsl:value-of select="$archiver"/>
			</xsl:element>
			<xsl:element name="archiverContact">
				<xsl:value-of select="$archiverContact"/>
			</xsl:element>
			<xsl:element name="dataOwner">
				<xsl:value-of select="$dataOwner"/>
			</xsl:element>
			<xsl:element name="dataOriginTimespan">
				<xsl:value-of select="$dataOriginTimespan"/>
			</xsl:element>
			<xsl:element name="producerApplication">
				<xsl:value-of select="$producerApplication"/>
			</xsl:element>
			<xsl:element name="archivalDate">
				<xsl:value-of select="$archivalDate"/>
			</xsl:element>
			<xsl:element name="messageDigest">
				<xsl:value-of select="$messageDigest"/>
			</xsl:element>
			<xsl:element name="clientMachine">
				<xsl:value-of select="$clientMachine"/>
			</xsl:element>
			<xsl:element name="databaseProduct">
				<xsl:value-of select="$databaseProduct"/>
			</xsl:element>
			<xsl:element name="connection">
				<xsl:value-of select="$connection"/>
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
						<xsl:value-of select="$databaseSchema"/>
					</xsl:element>
					<xsl:element name="folder">
						<xsl:value-of select="$databaseSchema"/>
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
					<xsl:if test="db:option[@key='query']/@value">
						<!-- Insert a solid line break  -->
						<xsl:text xml:space="preserve">\u000A</xsl:text>
						<xsl:text>QUERY: </xsl:text>
						<xsl:value-of select="db:option[@key='query']/@value"/>
						<xsl:text>;</xsl:text>
					</xsl:if>
				</xsl:element>
			</xsl:if>
			<!-- COLUMNS -->
			<xsl:element name="columns">
				<xsl:apply-templates/>
			</xsl:element>
			<!-- PRIMARY KEY -->
			<xsl:if test="db:column/@primaryKey='true'">
				<xsl:element name="primaryKey">
					<xsl:element name="name">
						<xsl:text>pk_</xsl:text>
						<xsl:value-of select="@name"/>
					</xsl:element>
					<xsl:for-each select="db:column">
						<xsl:if test="@primaryKey='true'">
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
				<xsl:choose>
					<!-- CHAR VARCHAR LONGVARCHAR CLOB -->
					<xsl:when test="@type='CHAR' or @type='VARCHAR' or @type='LONGVARCHAR' or @type='CLOB'">
						<xsl:text>CHARACTER VARYING</xsl:text>
							<xsl:choose>
								<xsl:when test="@size">
									<xsl:text>(</xsl:text>
									<xsl:value-of select="@size"/>
									<xsl:text>)</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>(255)</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
					</xsl:when>
					<!-- TINYINT SMALLINT INTEGER BIGINT -->
					<xsl:when test="@type='TINYINT' or @type='SMALLINT' or @type='INTEGER' or @type='BIGINT'">
						<xsl:text>INTEGER</xsl:text>
					</xsl:when>
					<!-- NUMERIC DECIMAL -->
					<xsl:when test="@type='NUMERIC' or @type='DECIMAL'">
						<xsl:text>NUMERIC</xsl:text>
						<xsl:if test="@size">
							<xsl:text>(</xsl:text>
							<xsl:value-of select="@size"/>
							<xsl:if test="@scale">
								<xsl:text>,</xsl:text>
								<xsl:value-of select="@scale"/>
							</xsl:if>
							<xsl:text>)</xsl:text>
						</xsl:if>
					</xsl:when>
					<!-- FLOAT REAL DOUBLE -->
					<xsl:when test="@type='FLOAT' or @type='REAL' or @type='DOUBLE'">
						<xsl:text>FLOAT</xsl:text>
						<xsl:if test="@size">
							<xsl:text>(</xsl:text>
							<xsl:value-of select="@size"/>
							<xsl:if test="@scale">
								<xsl:text>,</xsl:text>
								<xsl:value-of select="@scale"/>
							</xsl:if>
							<xsl:text>)</xsl:text>
						</xsl:if>
					</xsl:when>
					<!-- BINARY VARBINARY LONGVARBINARY -->
					<xsl:when test="@type='BINARY' or @type='VARBINARY' or @type='LONGVARBINARY'">
						<xsl:text>BIT VARYING</xsl:text>
							<xsl:choose>
								<xsl:when test="@size">
									<xsl:text>(</xsl:text>
									<xsl:value-of select="@size"/>
									<xsl:text>)</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>(4096)</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
					</xsl:when>
					<!-- BIT -->
					<xsl:when test="@type='BIT'">
						<xsl:text>BIT</xsl:text>
							<xsl:choose>
								<xsl:when test="@size">
									<xsl:text>(</xsl:text>
									<xsl:value-of select="@size"/>
									<xsl:text>)</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>(8)</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
					</xsl:when>
					<!-- BOOLEANINT BOOLEANCHAR -->
					<xsl:when test="@type='BOOLEANINT' or @type='BOOLEANCHAR'">
						<xsl:text>BOOLEAN</xsl:text>
					</xsl:when>
					<!-- BLOB -->
					<xsl:when test="@type='BLOB'">
						<xsl:text>BINARY LARGE OBJECT</xsl:text>
					</xsl:when>
					<!-- REF -->
					<xsl:when test="@type='REF'">
						<xsl:text>CHARACTER VARYING (255)</xsl:text>
					</xsl:when>
					<!-- all other data type -->
					<xsl:otherwise>
						<xsl:value-of select="@type"/>
						<xsl:if test="@size">
							<xsl:text>(</xsl:text>
							<xsl:value-of select="@size"/>
							<xsl:if test="@scale">
								<xsl:text>,</xsl:text>
								<xsl:value-of select="@scale"/>
							</xsl:if>
							<xsl:text>)</xsl:text>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:element>
			
			<xsl:element name="typeOriginal">
				<xsl:value-of select="@type"/>
				<xsl:if test="@size">
					<xsl:text>(</xsl:text>
					<xsl:value-of select="@size"/>
					<xsl:if test="@scale">
						<xsl:text>,</xsl:text>
						<xsl:value-of select="@scale"/>
					</xsl:if>
					<xsl:text>)</xsl:text>
				</xsl:if>
			</xsl:element>
			
			<xsl:element name="nullable">
				<xsl:choose>
					<xsl:when test="@required">
						<xsl:choose>
							<xsl:when test="@required='true'">
								<xsl:text>false</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>true</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>true</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:element>
			
			<xsl:if test="@description">
				<xsl:element name="description">
					<xsl:value-of select="@description"/>
				</xsl:element>
			</xsl:if>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
