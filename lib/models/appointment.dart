enum AppointmentStatus { upcoming, completed, cancelled }

class Appointment {
  final String id;
  final String doctorId;
  final String doctorName;
  final String doctorSpecialty;
  final String doctorHospital;
  final String date;
  final String timeSlot;
  final String patientName;
  final String patientPhone;
  final String patientReason;
  final double consultationFee;
  AppointmentStatus status;
  final DateTime createdAt;

  Appointment({
    required this.id,
    required this.doctorId,
    required this.doctorName,
    required this.doctorSpecialty,
    required this.doctorHospital,
    required this.date,
    required this.timeSlot,
    required this.patientName,
    required this.patientPhone,
    required this.patientReason,
    required this.consultationFee,
    this.status = AppointmentStatus.upcoming,
    DateTime? createdAt,
  }) : createdAt = createdAt ?? DateTime.now();
}
