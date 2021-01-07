<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\IpMessaging\V2;

use Twilio\Options;
use Twilio\Values;

abstract class ServiceOptions {
    /**
     * @param string $friendlyName The friendly_name
     * @param string $defaultServiceRoleSid The default_service_role_sid
     * @param string $defaultChannelRoleSid The default_channel_role_sid
     * @param string $defaultChannelCreatorRoleSid The
     *                                             default_channel_creator_role_sid
     * @param boolean $readStatusEnabled The read_status_enabled
     * @param boolean $reachabilityEnabled The reachability_enabled
     * @param integer $typingIndicatorTimeout The typing_indicator_timeout
     * @param integer $consumptionReportInterval The consumption_report_interval
     * @param boolean $notificationsNewMessageEnabled The
     *                                                notifications.new_message.enabled
     * @param string $notificationsNewMessageTemplate The
     *                                                notifications.new_message.template
     * @param string $notificationsNewMessageSound The
     *                                             notifications.new_message.sound
     * @param boolean $notificationsNewMessageBadgeCountEnabled The
     *                                                          notifications.new_message.badge_count_enabled
     * @param boolean $notificationsAddedToChannelEnabled The
     *                                                    notifications.added_to_channel.enabled
     * @param string $notificationsAddedToChannelTemplate The
     *                                                    notifications.added_to_channel.template
     * @param string $notificationsAddedToChannelSound The
     *                                                 notifications.added_to_channel.sound
     * @param boolean $notificationsRemovedFromChannelEnabled The
     *                                                        notifications.removed_from_channel.enabled
     * @param string $notificationsRemovedFromChannelTemplate The
     *                                                        notifications.removed_from_channel.template
     * @param string $notificationsRemovedFromChannelSound The
     *                                                     notifications.removed_from_channel.sound
     * @param boolean $notificationsInvitedToChannelEnabled The
     *                                                      notifications.invited_to_channel.enabled
     * @param string $notificationsInvitedToChannelTemplate The
     *                                                      notifications.invited_to_channel.template
     * @param string $notificationsInvitedToChannelSound The
     *                                                   notifications.invited_to_channel.sound
     * @param string $preWebhookUrl The pre_webhook_url
     * @param string $postWebhookUrl The post_webhook_url
     * @param string $webhookMethod The webhook_method
     * @param string $webhookFilters The webhook_filters
     * @param integer $limitsChannelMembers The limits.channel_members
     * @param integer $limitsUserChannels The limits.user_channels
     * @param string $mediaCompatibilityMessage The media.compatibility_message
     * @return UpdateServiceOptions Options builder
     */
    public static function update($friendlyName = Values::NONE, $defaultServiceRoleSid = Values::NONE, $defaultChannelRoleSid = Values::NONE, $defaultChannelCreatorRoleSid = Values::NONE, $readStatusEnabled = Values::NONE, $reachabilityEnabled = Values::NONE, $typingIndicatorTimeout = Values::NONE, $consumptionReportInterval = Values::NONE, $notificationsNewMessageEnabled = Values::NONE, $notificationsNewMessageTemplate = Values::NONE, $notificationsNewMessageSound = Values::NONE, $notificationsNewMessageBadgeCountEnabled = Values::NONE, $notificationsAddedToChannelEnabled = Values::NONE, $notificationsAddedToChannelTemplate = Values::NONE, $notificationsAddedToChannelSound = Values::NONE, $notificationsRemovedFromChannelEnabled = Values::NONE, $notificationsRemovedFromChannelTemplate = Values::NONE, $notificationsRemovedFromChannelSound = Values::NONE, $notificationsInvitedToChannelEnabled = Values::NONE, $notificationsInvitedToChannelTemplate = Values::NONE, $notificationsInvitedToChannelSound = Values::NONE, $preWebhookUrl = Values::NONE, $postWebhookUrl = Values::NONE, $webhookMethod = Values::NONE, $webhookFilters = Values::NONE, $limitsChannelMembers = Values::NONE, $limitsUserChannels = Values::NONE, $mediaCompatibilityMessage = Values::NONE) {
        return new UpdateServiceOptions($friendlyName, $defaultServiceRoleSid, $defaultChannelRoleSid, $defaultChannelCreatorRoleSid, $readStatusEnabled, $reachabilityEnabled, $typingIndicatorTimeout, $consumptionReportInterval, $notificationsNewMessageEnabled, $notificationsNewMessageTemplate, $notificationsNewMessageSound, $notificationsNewMessageBadgeCountEnabled, $notificationsAddedToChannelEnabled, $notificationsAddedToChannelTemplate, $notificationsAddedToChannelSound, $notificationsRemovedFromChannelEnabled, $notificationsRemovedFromChannelTemplate, $notificationsRemovedFromChannelSound, $notificationsInvitedToChannelEnabled, $notificationsInvitedToChannelTemplate, $notificationsInvitedToChannelSound, $preWebhookUrl, $postWebhookUrl, $webhookMethod, $webhookFilters, $limitsChannelMembers, $limitsUserChannels, $mediaCompatibilityMessage);
    }
}

class UpdateServiceOptions extends Options {
    /**
     * @param string $friendlyName The friendly_name
     * @param string $defaultServiceRoleSid The default_service_role_sid
     * @param string $defaultChannelRoleSid The default_channel_role_sid
     * @param string $defaultChannelCreatorRoleSid The
     *                                             default_channel_creator_role_sid
     * @param boolean $readStatusEnabled The read_status_enabled
     * @param boolean $reachabilityEnabled The reachability_enabled
     * @param integer $typingIndicatorTimeout The typing_indicator_timeout
     * @param integer $consumptionReportInterval The consumption_report_interval
     * @param boolean $notificationsNewMessageEnabled The
     *                                                notifications.new_message.enabled
     * @param string $notificationsNewMessageTemplate The
     *                                                notifications.new_message.template
     * @param string $notificationsNewMessageSound The
     *                                             notifications.new_message.sound
     * @param boolean $notificationsNewMessageBadgeCountEnabled The
     *                                                          notifications.new_message.badge_count_enabled
     * @param boolean $notificationsAddedToChannelEnabled The
     *                                                    notifications.added_to_channel.enabled
     * @param string $notificationsAddedToChannelTemplate The
     *                                                    notifications.added_to_channel.template
     * @param string $notificationsAddedToChannelSound The
     *                                                 notifications.added_to_channel.sound
     * @param boolean $notificationsRemovedFromChannelEnabled The
     *                                                        notifications.removed_from_channel.enabled
     * @param string $notificationsRemovedFromChannelTemplate The
     *                                                        notifications.removed_from_channel.template
     * @param string $notificationsRemovedFromChannelSound The
     *                                                     notifications.removed_from_channel.sound
     * @param boolean $notificationsInvitedToChannelEnabled The
     *                                                      notifications.invited_to_channel.enabled
     * @param string $notificationsInvitedToChannelTemplate The
     *                                                      notifications.invited_to_channel.template
     * @param string $notificationsInvitedToChannelSound The
     *                                                   notifications.invited_to_channel.sound
     * @param string $preWebhookUrl The pre_webhook_url
     * @param string $postWebhookUrl The post_webhook_url
     * @param string $webhookMethod The webhook_method
     * @param string $webhookFilters The webhook_filters
     * @param integer $limitsChannelMembers The limits.channel_members
     * @param integer $limitsUserChannels The limits.user_channels
     * @param string $mediaCompatibilityMessage The media.compatibility_message
     */
    public function __construct($friendlyName = Values::NONE, $defaultServiceRoleSid = Values::NONE, $defaultChannelRoleSid = Values::NONE, $defaultChannelCreatorRoleSid = Values::NONE, $readStatusEnabled = Values::NONE, $reachabilityEnabled = Values::NONE, $typingIndicatorTimeout = Values::NONE, $consumptionReportInterval = Values::NONE, $notificationsNewMessageEnabled = Values::NONE, $notificationsNewMessageTemplate = Values::NONE, $notificationsNewMessageSound = Values::NONE, $notificationsNewMessageBadgeCountEnabled = Values::NONE, $notificationsAddedToChannelEnabled = Values::NONE, $notificationsAddedToChannelTemplate = Values::NONE, $notificationsAddedToChannelSound = Values::NONE, $notificationsRemovedFromChannelEnabled = Values::NONE, $notificationsRemovedFromChannelTemplate = Values::NONE, $notificationsRemovedFromChannelSound = Values::NONE, $notificationsInvitedToChannelEnabled = Values::NONE, $notificationsInvitedToChannelTemplate = Values::NONE, $notificationsInvitedToChannelSound = Values::NONE, $preWebhookUrl = Values::NONE, $postWebhookUrl = Values::NONE, $webhookMethod = Values::NONE, $webhookFilters = Values::NONE, $limitsChannelMembers = Values::NONE, $limitsUserChannels = Values::NONE, $mediaCompatibilityMessage = Values::NONE) {
        $this->options['friendlyName'] = $friendlyName;
        $this->options['defaultServiceRoleSid'] = $defaultServiceRoleSid;
        $this->options['defaultChannelRoleSid'] = $defaultChannelRoleSid;
        $this->options['defaultChannelCreatorRoleSid'] = $defaultChannelCreatorRoleSid;
        $this->options['readStatusEnabled'] = $readStatusEnabled;
        $this->options['reachabilityEnabled'] = $reachabilityEnabled;
        $this->options['typingIndicatorTimeout'] = $typingIndicatorTimeout;
        $this->options['consumptionReportInterval'] = $consumptionReportInterval;
        $this->options['notificationsNewMessageEnabled'] = $notificationsNewMessageEnabled;
        $this->options['notificationsNewMessageTemplate'] = $notificationsNewMessageTemplate;
        $this->options['notificationsNewMessageSound'] = $notificationsNewMessageSound;
        $this->options['notificationsNewMessageBadgeCountEnabled'] = $notificationsNewMessageBadgeCountEnabled;
        $this->options['notificationsAddedToChannelEnabled'] = $notificationsAddedToChannelEnabled;
        $this->options['notificationsAddedToChannelTemplate'] = $notificationsAddedToChannelTemplate;
        $this->options['notificationsAddedToChannelSound'] = $notificationsAddedToChannelSound;
        $this->options['notificationsRemovedFromChannelEnabled'] = $notificationsRemovedFromChannelEnabled;
        $this->options['notificationsRemovedFromChannelTemplate'] = $notificationsRemovedFromChannelTemplate;
        $this->options['notificationsRemovedFromChannelSound'] = $notificationsRemovedFromChannelSound;
        $this->options['notificationsInvitedToChannelEnabled'] = $notificationsInvitedToChannelEnabled;
        $this->options['notificationsInvitedToChannelTemplate'] = $notificationsInvitedToChannelTemplate;
        $this->options['notificationsInvitedToChannelSound'] = $notificationsInvitedToChannelSound;
        $this->options['preWebhookUrl'] = $preWebhookUrl;
        $this->options['postWebhookUrl'] = $postWebhookUrl;
        $this->options['webhookMethod'] = $webhookMethod;
        $this->options['webhookFilters'] = $webhookFilters;
        $this->options['limitsChannelMembers'] = $limitsChannelMembers;
        $this->options['limitsUserChannels'] = $limitsUserChannels;
        $this->options['mediaCompatibilityMessage'] = $mediaCompatibilityMessage;
    }

    /**
     * The friendly_name
     * 
     * @param string $friendlyName The friendly_name
     * @return $this Fluent Builder
     */
    public function setFriendlyName($friendlyName) {
        $this->options['friendlyName'] = $friendlyName;
        return $this;
    }

    /**
     * The default_service_role_sid
     * 
     * @param string $defaultServiceRoleSid The default_service_role_sid
     * @return $this Fluent Builder
     */
    public function setDefaultServiceRoleSid($defaultServiceRoleSid) {
        $this->options['defaultServiceRoleSid'] = $defaultServiceRoleSid;
        return $this;
    }

    /**
     * The default_channel_role_sid
     * 
     * @param string $defaultChannelRoleSid The default_channel_role_sid
     * @return $this Fluent Builder
     */
    public function setDefaultChannelRoleSid($defaultChannelRoleSid) {
        $this->options['defaultChannelRoleSid'] = $defaultChannelRoleSid;
        return $this;
    }

    /**
     * The default_channel_creator_role_sid
     * 
     * @param string $defaultChannelCreatorRoleSid The
     *                                             default_channel_creator_role_sid
     * @return $this Fluent Builder
     */
    public function setDefaultChannelCreatorRoleSid($defaultChannelCreatorRoleSid) {
        $this->options['defaultChannelCreatorRoleSid'] = $defaultChannelCreatorRoleSid;
        return $this;
    }

    /**
     * The read_status_enabled
     * 
     * @param boolean $readStatusEnabled The read_status_enabled
     * @return $this Fluent Builder
     */
    public function setReadStatusEnabled($readStatusEnabled) {
        $this->options['readStatusEnabled'] = $readStatusEnabled;
        return $this;
    }

    /**
     * The reachability_enabled
     * 
     * @param boolean $reachabilityEnabled The reachability_enabled
     * @return $this Fluent Builder
     */
    public function setReachabilityEnabled($reachabilityEnabled) {
        $this->options['reachabilityEnabled'] = $reachabilityEnabled;
        return $this;
    }

    /**
     * The typing_indicator_timeout
     * 
     * @param integer $typingIndicatorTimeout The typing_indicator_timeout
     * @return $this Fluent Builder
     */
    public function setTypingIndicatorTimeout($typingIndicatorTimeout) {
        $this->options['typingIndicatorTimeout'] = $typingIndicatorTimeout;
        return $this;
    }

    /**
     * The consumption_report_interval
     * 
     * @param integer $consumptionReportInterval The consumption_report_interval
     * @return $this Fluent Builder
     */
    public function setConsumptionReportInterval($consumptionReportInterval) {
        $this->options['consumptionReportInterval'] = $consumptionReportInterval;
        return $this;
    }

    /**
     * The notifications.new_message.enabled
     * 
     * @param boolean $notificationsNewMessageEnabled The
     *                                                notifications.new_message.enabled
     * @return $this Fluent Builder
     */
    public function setNotificationsNewMessageEnabled($notificationsNewMessageEnabled) {
        $this->options['notificationsNewMessageEnabled'] = $notificationsNewMessageEnabled;
        return $this;
    }

    /**
     * The notifications.new_message.template
     * 
     * @param string $notificationsNewMessageTemplate The
     *                                                notifications.new_message.template
     * @return $this Fluent Builder
     */
    public function setNotificationsNewMessageTemplate($notificationsNewMessageTemplate) {
        $this->options['notificationsNewMessageTemplate'] = $notificationsNewMessageTemplate;
        return $this;
    }

    /**
     * The notifications.new_message.sound
     * 
     * @param string $notificationsNewMessageSound The
     *                                             notifications.new_message.sound
     * @return $this Fluent Builder
     */
    public function setNotificationsNewMessageSound($notificationsNewMessageSound) {
        $this->options['notificationsNewMessageSound'] = $notificationsNewMessageSound;
        return $this;
    }

    /**
     * The notifications.new_message.badge_count_enabled
     * 
     * @param boolean $notificationsNewMessageBadgeCountEnabled The
     *                                                          notifications.new_message.badge_count_enabled
     * @return $this Fluent Builder
     */
    public function setNotificationsNewMessageBadgeCountEnabled($notificationsNewMessageBadgeCountEnabled) {
        $this->options['notificationsNewMessageBadgeCountEnabled'] = $notificationsNewMessageBadgeCountEnabled;
        return $this;
    }

    /**
     * The notifications.added_to_channel.enabled
     * 
     * @param boolean $notificationsAddedToChannelEnabled The
     *                                                    notifications.added_to_channel.enabled
     * @return $this Fluent Builder
     */
    public function setNotificationsAddedToChannelEnabled($notificationsAddedToChannelEnabled) {
        $this->options['notificationsAddedToChannelEnabled'] = $notificationsAddedToChannelEnabled;
        return $this;
    }

    /**
     * The notifications.added_to_channel.template
     * 
     * @param string $notificationsAddedToChannelTemplate The
     *                                                    notifications.added_to_channel.template
     * @return $this Fluent Builder
     */
    public function setNotificationsAddedToChannelTemplate($notificationsAddedToChannelTemplate) {
        $this->options['notificationsAddedToChannelTemplate'] = $notificationsAddedToChannelTemplate;
        return $this;
    }

    /**
     * The notifications.added_to_channel.sound
     * 
     * @param string $notificationsAddedToChannelSound The
     *                                                 notifications.added_to_channel.sound
     * @return $this Fluent Builder
     */
    public function setNotificationsAddedToChannelSound($notificationsAddedToChannelSound) {
        $this->options['notificationsAddedToChannelSound'] = $notificationsAddedToChannelSound;
        return $this;
    }

    /**
     * The notifications.removed_from_channel.enabled
     * 
     * @param boolean $notificationsRemovedFromChannelEnabled The
     *                                                        notifications.removed_from_channel.enabled
     * @return $this Fluent Builder
     */
    public function setNotificationsRemovedFromChannelEnabled($notificationsRemovedFromChannelEnabled) {
        $this->options['notificationsRemovedFromChannelEnabled'] = $notificationsRemovedFromChannelEnabled;
        return $this;
    }

    /**
     * The notifications.removed_from_channel.template
     * 
     * @param string $notificationsRemovedFromChannelTemplate The
     *                                                        notifications.removed_from_channel.template
     * @return $this Fluent Builder
     */
    public function setNotificationsRemovedFromChannelTemplate($notificationsRemovedFromChannelTemplate) {
        $this->options['notificationsRemovedFromChannelTemplate'] = $notificationsRemovedFromChannelTemplate;
        return $this;
    }

    /**
     * The notifications.removed_from_channel.sound
     * 
     * @param string $notificationsRemovedFromChannelSound The
     *                                                     notifications.removed_from_channel.sound
     * @return $this Fluent Builder
     */
    public function setNotificationsRemovedFromChannelSound($notificationsRemovedFromChannelSound) {
        $this->options['notificationsRemovedFromChannelSound'] = $notificationsRemovedFromChannelSound;
        return $this;
    }

    /**
     * The notifications.invited_to_channel.enabled
     * 
     * @param boolean $notificationsInvitedToChannelEnabled The
     *                                                      notifications.invited_to_channel.enabled
     * @return $this Fluent Builder
     */
    public function setNotificationsInvitedToChannelEnabled($notificationsInvitedToChannelEnabled) {
        $this->options['notificationsInvitedToChannelEnabled'] = $notificationsInvitedToChannelEnabled;
        return $this;
    }

    /**
     * The notifications.invited_to_channel.template
     * 
     * @param string $notificationsInvitedToChannelTemplate The
     *                                                      notifications.invited_to_channel.template
     * @return $this Fluent Builder
     */
    public function setNotificationsInvitedToChannelTemplate($notificationsInvitedToChannelTemplate) {
        $this->options['notificationsInvitedToChannelTemplate'] = $notificationsInvitedToChannelTemplate;
        return $this;
    }

    /**
     * The notifications.invited_to_channel.sound
     * 
     * @param string $notificationsInvitedToChannelSound The
     *                                                   notifications.invited_to_channel.sound
     * @return $this Fluent Builder
     */
    public function setNotificationsInvitedToChannelSound($notificationsInvitedToChannelSound) {
        $this->options['notificationsInvitedToChannelSound'] = $notificationsInvitedToChannelSound;
        return $this;
    }

    /**
     * The pre_webhook_url
     * 
     * @param string $preWebhookUrl The pre_webhook_url
     * @return $this Fluent Builder
     */
    public function setPreWebhookUrl($preWebhookUrl) {
        $this->options['preWebhookUrl'] = $preWebhookUrl;
        return $this;
    }

    /**
     * The post_webhook_url
     * 
     * @param string $postWebhookUrl The post_webhook_url
     * @return $this Fluent Builder
     */
    public function setPostWebhookUrl($postWebhookUrl) {
        $this->options['postWebhookUrl'] = $postWebhookUrl;
        return $this;
    }

    /**
     * The webhook_method
     * 
     * @param string $webhookMethod The webhook_method
     * @return $this Fluent Builder
     */
    public function setWebhookMethod($webhookMethod) {
        $this->options['webhookMethod'] = $webhookMethod;
        return $this;
    }

    /**
     * The webhook_filters
     * 
     * @param string $webhookFilters The webhook_filters
     * @return $this Fluent Builder
     */
    public function setWebhookFilters($webhookFilters) {
        $this->options['webhookFilters'] = $webhookFilters;
        return $this;
    }

    /**
     * The limits.channel_members
     * 
     * @param integer $limitsChannelMembers The limits.channel_members
     * @return $this Fluent Builder
     */
    public function setLimitsChannelMembers($limitsChannelMembers) {
        $this->options['limitsChannelMembers'] = $limitsChannelMembers;
        return $this;
    }

    /**
     * The limits.user_channels
     * 
     * @param integer $limitsUserChannels The limits.user_channels
     * @return $this Fluent Builder
     */
    public function setLimitsUserChannels($limitsUserChannels) {
        $this->options['limitsUserChannels'] = $limitsUserChannels;
        return $this;
    }

    /**
     * The media.compatibility_message
     * 
     * @param string $mediaCompatibilityMessage The media.compatibility_message
     * @return $this Fluent Builder
     */
    public function setMediaCompatibilityMessage($mediaCompatibilityMessage) {
        $this->options['mediaCompatibilityMessage'] = $mediaCompatibilityMessage;
        return $this;
    }

    /**
     * Provide a friendly representation
     * 
     * @return string Machine friendly representation
     */
    public function __toString() {
        $options = array();
        foreach ($this->options as $key => $value) {
            if ($value != Values::NONE) {
                $options[] = "$key=$value";
            }
        }
        return '[Twilio.IpMessaging.V2.UpdateServiceOptions ' . implode(' ', $options) . ']';
    }
}