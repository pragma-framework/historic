# Pragma Historic

The Pragma Module for handling models changelogs in Pragma Framework.

## Installation

In composer.json add:

	require {"pragma-framework/historic": "dev-master"}

And in scripts blocks:

	"scripts": {
		"post-install-cmd": [
			"Pragma\\Historic\\Helpers\\Migrate::postInstallCmd"
		],
		"post-update-cmd": [
			"Pragma\\Historic\\Helpers\\Migrate::postUpdateCmd"
		]
	}


