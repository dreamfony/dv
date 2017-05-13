Moderation State Machine is mostly a module intended for developers.
Although it provides a block (Moderation state switcher) with state transition action buttons, its biggest power comes when writing plugins.

Drupal's Workflow and Content Moderation (https://www.drupal.org/docs/8/core/modules/content-moderation/overview) modules provide UI for configuring Moderation states and transitions.
And attaching configured workflows to entities.
What it does not provide is a developer friendly state machine interface like https://www.drupal.org/project/state_machine
This is where this module comes in.
It allows developers to write plugins of type "ModerationStatePlugin".
Plugins can subscribe to state transition events and define state change validations and violation message.
Module also provides API to change state (TODO ? switch)

TODO write example plugin