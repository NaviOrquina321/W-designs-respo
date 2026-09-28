import 'package:flutter/material.dart';

class StudentItem {
  final String id;
  final String name;
  final String email;
  final String gradeLevel;
  bool isValidated;

  StudentItem({
    required this.id,
    required this.name,
    required this.email,
    required this.gradeLevel,
    this.isValidated = false,
  });
}

class MatchingSession {
  final String id;
  final String studentName;
  final String tutorName;
  final String subject;
  final String matchScore;
  String status; // 'Pending', 'Approved', 'Cancelled'

  MatchingSession({
    required this.id,
    required this.studentName,
    required this.tutorName,
    required this.subject,
    required this.matchScore,
    this.status = 'Pending',
  });
}

class ScheduleSession {
  final String id;
  final String title;
  final String dateTime;
  final String tutorName;
  final String studentName;

  ScheduleSession({
    required this.id,
    required this.title,
    required this.dateTime,
    required this.tutorName,
    required this.studentName,
  });
}

class PaymentRecord {
  final String id;
  final String payerName;
  final double amount;
  final String date;
  String status; // 'Pending', 'Confirmed'

  PaymentRecord({
    required this.id,
    required this.payerName,
    required this.amount,
    required this.date,
    this.status = 'Pending',
  });
}

class AdminNotification {
  final String id;
  final String title;
  final String message;
  final String recipientGroup;
  final DateTime timestamp;

  AdminNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.recipientGroup,
    DateTime? timestamp,
  }) : timestamp = timestamp ?? DateTime.now();
}

class TutoringReport {
  final String id;
  final String tutorName;
  final String studentName;
  final String subject;
  final int totalHours;
  final String period; // 'Weekly', 'Monthly', 'Completed'

  TutoringReport({
    required this.id,
    required this.tutorName,
    required this.studentName,
    required this.subject,
    required this.totalHours,
    required this.period,
  });
}

class AdminStore extends ChangeNotifier {
  final List<StudentItem> _students = [
    StudentItem(
        id: 'st_1',
        name: 'Jordan Lee',
        email: 'jordan@example.com',
        gradeLevel: 'Grade 10',
        isValidated: true),
    StudentItem(
        id: 'st_2',
        name: 'Samantha Green',
        email: 'sam@example.com',
        gradeLevel: 'Grade 11',
        isValidated: false),
    StudentItem(
        id: 'st_3',
        name: 'David Kim',
        email: 'david.k@example.com',
        gradeLevel: 'Grade 12',
        isValidated: false),
  ];

  final List<MatchingSession> _matchings = [
    MatchingSession(
        id: 'm_1',
        studentName: 'Jordan Lee',
        tutorName: 'Dr. Sarah Jenkins',
        subject: 'Biology & Health',
        matchScore: '98% Match',
        status: 'Pending'),
    MatchingSession(
        id: 'm_2',
        studentName: 'Samantha Green',
        tutorName: 'Dr. Marcus Vance',
        subject: 'Dermatology Studies',
        matchScore: '92% Match',
        status: 'Pending'),
  ];

  final List<ScheduleSession> _schedules = [
    ScheduleSession(
        id: 'sch_1',
        title: 'Cardiology 101 Lecture',
        dateTime: 'Mon Oct 12, 10:00 AM',
        tutorName: 'Dr. Sarah Jenkins',
        studentName: 'Jordan Lee'),
    ScheduleSession(
        id: 'sch_2',
        title: 'Advanced Dermatology Session',
        dateTime: 'Wed Oct 14, 02:00 PM',
        tutorName: 'Dr. Marcus Vance',
        studentName: 'Samantha Green'),
  ];

  final List<PaymentRecord> _payments = [
    PaymentRecord(
        id: 'pay_1',
        payerName: 'Jordan Lee',
        amount: 120.00,
        date: '2026-09-20',
        status: 'Confirmed'),
    PaymentRecord(
        id: 'pay_2',
        payerName: 'Samantha Green',
        amount: 95.00,
        date: '2026-09-25',
        status: 'Pending'),
  ];

  final List<AdminNotification> _notifications = [
    AdminNotification(
        id: 'notif_1',
        title: 'Schedule Updated',
        message: 'Your tutoring session for Monday has been rescheduled.',
        recipientGroup: 'All Students'),
  ];

  final List<TutoringReport> _reports = [
    TutoringReport(
        id: 'rep_1',
        tutorName: 'Dr. Sarah Jenkins',
        studentName: 'Jordan Lee',
        subject: 'Cardiology Support',
        totalHours: 12,
        period: 'Completed'),
    TutoringReport(
        id: 'rep_2',
        tutorName: 'Dr. Marcus Vance',
        studentName: 'Samantha Green',
        subject: 'Skin Science Basics',
        totalHours: 4,
        period: 'Weekly'),
    TutoringReport(
        id: 'rep_3',
        tutorName: 'Dr. Emily Chen',
        studentName: 'David Kim',
        subject: 'Neuroscience Intro',
        totalHours: 16,
        period: 'Monthly'),
  ];

  List<StudentItem> get students => List.unmodifiable(_students);
  List<MatchingSession> get matchings => List.unmodifiable(_matchings);
  List<ScheduleSession> get schedules => List.unmodifiable(_schedules);
  List<PaymentRecord> get payments => List.unmodifiable(_payments);
  List<AdminNotification> get notifications => List.unmodifiable(_notifications);
  List<TutoringReport> get reports => List.unmodifiable(_reports);

  void validateStudent(String studentId) {
    final index = _students.indexWhere((s) => s.id == studentId);
    if (index != -1) {
      _students[index].isValidated = true;
      notifyListeners();
    }
  }

  void approveMatching(String matchingId) {
    final index = _matchings.indexWhere((m) => m.id == matchingId);
    if (index != -1) {
      _matchings[index].status = 'Approved';
      notifyListeners();
    }
  }

  void cancelMatching(String matchingId) {
    final index = _matchings.indexWhere((m) => m.id == matchingId);
    if (index != -1) {
      _matchings[index].status = 'Cancelled';
      notifyListeners();
    }
  }

  void addSchedule(ScheduleSession schedule) {
    _schedules.add(schedule);
    notifyListeners();
  }

  void addPayment(PaymentRecord payment) {
    _payments.insert(0, payment);
    notifyListeners();
  }

  void confirmPayment(String paymentId) {
    final index = _payments.indexWhere((p) => p.id == paymentId);
    if (index != -1) {
      _payments[index].status = 'Confirmed';
      notifyListeners();
    }
  }

  void sendNotification(AdminNotification notification) {
    _notifications.insert(0, notification);
    notifyListeners();
  }
}
