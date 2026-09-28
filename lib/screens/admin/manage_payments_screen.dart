import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/admin_store.dart';

class ManagePaymentsScreen extends StatefulWidget {
  final int initialTab;

  const ManagePaymentsScreen({super.key, this.initialTab = 0});

  @override
  State<ManagePaymentsScreen> createState() => _ManagePaymentsScreenState();
}

class _ManagePaymentsScreenState extends State<ManagePaymentsScreen> {
  final _payerController = TextEditingController();
  final _amountController = TextEditingController();
  final _dateController = TextEditingController();

  @override
  void dispose() {
    _payerController.dispose();
    _amountController.dispose();
    _dateController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      initialIndex: widget.initialTab,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Manage Payments'),
          backgroundColor: const Color(0xFF081C29),
          foregroundColor: Colors.white,
          bottom: const TabBar(
            indicatorColor: Colors.cyanAccent,
            labelColor: Colors.cyanAccent,
            unselectedLabelColor: Colors.white70,
            tabs: [
              Tab(text: 'New Payment Records'),
              Tab(text: 'Confirm Payment Status'),
            ],
          ),
        ),
        body: Consumer<AdminStore>(
          builder: (context, store, child) {
            return TabBarView(
              children: [
                _buildNewPaymentForm(context, store),
                _buildConfirmPaymentList(context, store),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildNewPaymentForm(BuildContext context, AdminStore store) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Card(
        elevation: 2,
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Record New Payment',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _payerController,
                decoration: const InputDecoration(
                  labelText: 'Payer Name',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.person),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _amountController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Amount (\$) ',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.attach_money),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _dateController,
                decoration: const InputDecoration(
                  labelText: 'Payment Date (YYYY-MM-DD)',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.calendar_today),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blueAccent,
                    foregroundColor: Colors.white,
                  ),
                  onPressed: () {
                    if (_payerController.text.isNotEmpty &&
                        _amountController.text.isNotEmpty) {
                      final amount =
                          double.tryParse(_amountController.text) ?? 100.0;
                      final dateStr = _dateController.text.isEmpty
                          ? DateTime.now().toString().split(' ').first
                          : _dateController.text;

                      final record = PaymentRecord(
                        id: 'pay_${DateTime.now().millisecondsSinceEpoch}',
                        payerName: _payerController.text,
                        amount: amount,
                        date: dateStr,
                        status: 'Pending',
                      );
                      store.addPayment(record);

                      _payerController.clear();
                      _amountController.clear();
                      _dateController.clear();

                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                            content: Text('Payment Record Created!')),
                      );
                    }
                  },
                  child: const Text('Save Payment Record',
                      style: TextStyle(fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildConfirmPaymentList(BuildContext context, AdminStore store) {
    final payments = store.payments;

    if (payments.isEmpty) {
      return const Center(child: Text('No payment records found.'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: payments.length,
      itemBuilder: (context, index) {
        final payment = payments[index];
        final isConfirmed = payment.status == 'Confirmed';

        return Card(
          elevation: 2,
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor:
                  isConfirmed ? Colors.green.withAlpha(30) : Colors.orange.withAlpha(30),
              child: Icon(
                isConfirmed ? Icons.check_circle : Icons.pending,
                color: isConfirmed ? Colors.green : Colors.orange,
              ),
            ),
            title: Text('${payment.payerName} - \$${payment.amount.toStringAsFixed(2)}',
                style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('Date: ${payment.date} • Status: ${payment.status}'),
            trailing: isConfirmed
                ? const Icon(Icons.verified, color: Colors.green)
                : ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      foregroundColor: Colors.white,
                    ),
                    onPressed: () {
                      store.confirmPayment(payment.id);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                            content: Text('Payment Status Confirmed!')),
                      );
                    },
                    child: const Text('Confirm'),
                  ),
          ),
        );
      },
    );
  }
}
