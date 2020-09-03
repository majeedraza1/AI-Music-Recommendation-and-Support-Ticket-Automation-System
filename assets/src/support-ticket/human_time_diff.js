const human_time_diff = function (from, to = 0) {
  if (typeof from !== "number" || typeof to !== "number") {
    throw 'Parameter from and to must be integer.';
  }

  if (!to) {
    to = Date.now() / 1000;
  }

  const date1 = new Date(from);
  const date2 = new Date(to);

  console.log(['working', from, to]);
}

export {human_time_diff}
export default human_time_diff;