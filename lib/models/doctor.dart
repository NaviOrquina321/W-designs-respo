class Doctor {
  final String id;
  final String name;
  final String specialty;
  final String categoryId;
  final double rating;
  final int reviewCount;
  final int yearsExperience;
  final double consultationFee;
  final String hospital;
  final String about;
  final String imageUrl;
  final List<String> availableDays;
  final List<String> availableTimeSlots;
  bool isFavorite;

  Doctor({
    required this.id,
    required this.name,
    required this.specialty,
    required this.categoryId,
    required this.rating,
    required this.reviewCount,
    required this.yearsExperience,
    required this.consultationFee,
    required this.hospital,
    required this.about,
    required this.imageUrl,
    required this.availableDays,
    required this.availableTimeSlots,
    this.isFavorite = false,
  });
}
