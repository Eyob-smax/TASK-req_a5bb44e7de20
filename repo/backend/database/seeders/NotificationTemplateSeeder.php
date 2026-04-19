<?php

namespace Database\Seeders;

use App\Enums\NotificationCategory;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['type' => 'enrollment.approved', 'category' => NotificationCategory::System, 'title_template' => 'Enrollment approved', 'body_template' => 'Your enrollment in {section_code} has been approved.'],
            ['type' => 'enrollment.denied', 'category' => NotificationCategory::System, 'title_template' => 'Enrollment denied', 'body_template' => 'Your enrollment in {section_code} was not approved. Reason: {reason}'],
            ['type' => 'grade.published', 'category' => NotificationCategory::System, 'title_template' => 'Grade published: {grade_item}', 'body_template' => 'Your grade for {grade_item} in {section_code} is now available.'],
            ['type' => 'appointment.rescheduled', 'category' => NotificationCategory::System, 'title_template' => 'Appointment rescheduled', 'body_template' => 'Appointment {resource_ref} has been moved to {scheduled_start}.'],
            ['type' => 'appointment.canceled', 'category' => NotificationCategory::System, 'title_template' => 'Appointment canceled', 'body_template' => 'Appointment {resource_ref} scheduled for {scheduled_start} has been canceled.'],
            ['type' => 'discussion.mention', 'category' => NotificationCategory::Mentions, 'title_template' => '{actor} mentioned you', 'body_template' => '{actor} mentioned you in "{title}".'],
            ['type' => 'announcement.posted', 'category' => NotificationCategory::Announcements, 'title_template' => 'New announcement: {title}', 'body_template' => '{actor} posted a new announcement in {course_code}.'],
            ['type' => 'billing.initial', 'category' => NotificationCategory::Billing, 'title_template' => 'Bill issued', 'body_template' => 'A new bill of {amount} has been issued, due on {due_on}.'],
            ['type' => 'billing.recurring', 'category' => NotificationCategory::Billing, 'title_template' => 'Recurring bill issued', 'body_template' => 'Your recurring bill of {amount} has been issued, due on {due_on}.'],
            ['type' => 'billing.penalty', 'category' => NotificationCategory::Billing, 'title_template' => 'Late-payment penalty applied', 'body_template' => 'A late-payment penalty of {amount} has been applied to bill #{bill_id}.'],
            ['type' => 'billing.refund', 'category' => NotificationCategory::Billing, 'title_template' => 'Refund processed', 'body_template' => 'A refund of {amount} has been processed on bill #{bill_id}.'],
            ['type' => 'billing.paid', 'category' => NotificationCategory::Billing, 'title_template' => 'Payment received', 'body_template' => 'Thank you — a payment of {amount} has been recorded.'],
            ['type' => 'moderation.content-hidden', 'category' => NotificationCategory::System, 'title_template' => 'Content hidden by moderator', 'body_template' => 'Your {target_type} was hidden. Notes: {notes}'],
            ['type' => 'moderation.content-locked', 'category' => NotificationCategory::System, 'title_template' => 'Thread locked', 'body_template' => 'Thread "{title}" has been locked by a moderator.'],
        ];

        foreach ($rows as $row) {
            NotificationTemplate::updateOrCreate(['type' => $row['type']], $row);
        }
    }
}
