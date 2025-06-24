from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np
import datetime
import yfinance as yf

from tensorflow.keras.models import load_model

app = Flask(__name__)
CORS(app)

MODELS = {
    "lstm": "model_lstm.h5",
    "gru": "model_gru.h5",
    "lstm_gru": "model_lstm_gru.h5"
}
WINDOW_SIZE = 60  # ganti sesuai input model kamu

def get_stock_data(ticker, window=WINDOW_SIZE):
    df = yf.download(ticker + ".JK", period="2y")
    if df.empty:
        return None
    close_prices = df['Close'].values
    if len(close_prices) < window:
        return None
    return close_prices[-window:]

def load_selected_model(model_name):
    path = MODELS.get(model_name)
    if not path:
        return None
    return load_model(path)

@app.route('/predict', methods=['GET'])
def predict():
    ticker = request.args.get('ticker', 'BBCA')
    n_days = int(request.args.get('days', 30))
    model_name = request.args.get('model', 'lstm')
    last_prices = get_stock_data(ticker)
    if last_prices is None:
        return jsonify({'error': 'Data saham tidak ditemukan atau terlalu sedikit.'}), 400
    min_p = last_prices.min()
    max_p = last_prices.max()
    scaled = (last_prices - min_p) / (max_p - min_p + 1e-8)
    input_data = scaled.reshape(1, WINDOW_SIZE, 1)
    model = load_selected_model(model_name)
    if model is None:
        return jsonify({'error': 'Model tidak ditemukan.'}), 400
    preds = []
    for i in range(n_days):
        pred = model.predict(input_data, verbose=0)
        pred_price = pred[0,0] * (max_p - min_p) + min_p
        preds.append(float(pred_price))
        input_data = np.append(input_data[:,1:,:], [[[pred[0,0]]]], axis=1)
    start_date = datetime.date(2025, 1, 1)
    dates = [(start_date + datetime.timedelta(days=i)).isoformat() for i in range(n_days)]
    return jsonify({
        'dates': dates,
        'prices': preds
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)