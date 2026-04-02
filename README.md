# Local Profilefield Repeatable

Local plugin to manage reference dictionaries for repeatable profile fields.

## Features

- Create and update reference domains
- Import code and label pairs from CSV
- Resolve labels by code via web services

## Installation

1. Copy this plugin to `local/profilefield_repeatable`.
2. Visit the Site Administration notifications page to complete the installation.

## Usage

- Go to Site Administration -> Plugins -> Local plugins -> Manage repeatable reference dictionaries.
- Create a domain and import CSV content or upload a CSV file.

## Web Services

This plugin provides web service functions for upserting reference items and resolving labels.
You must grant the capability `local/profilefield_repeatable:managereference` to allow access.
